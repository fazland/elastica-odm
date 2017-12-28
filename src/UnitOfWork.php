<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica;

use Doctrine\Common\EventManager;
use Elastica\Document;
use Fazland\ODM\Elastica\Exception\InvalidIdentifierException;
use Fazland\ODM\Elastica\Metadata\DocumentMetadata;
use Fazland\ODM\Elastica\Metadata\FieldMetadata;
use Fazland\ODM\Elastica\Persister\DocumentPersister;

final class UnitOfWork
{
    const STATE_MANAGED = 1;
    const STATE_NEW = 2;
    const STATE_DETACHED = 3;
    const STATE_REMOVED = 4;

    /**
     * Map documents by identifiers.
     *
     * @var object[]
     */
    private $identityMap = [];

    /**
     * Map of all attached documents by object hash.
     *
     * @var object[]
     */
    private $objects = [];

    /**
     * Map of the original document data of managed documents.
     * Keys are object hash. This is used for calculating changesets at commit time.
     *
     * @var array
     */
    private $originalDocumentData = [];

    /**
     * Map of the document states.
     * Keys are object hash. Note that only MANAGED and REMOVED states are known,
     * as DETACHED documents can be gc'd and the associated hashes can be re-used.
     *
     * @var array
     */
    private $documentStates = [];

    /**
     * Map of document persister by class name.
     *
     * @var DocumentPersister[]
     */
    private $documentPersisters = [];

    /**
     * The document manager associated with this unit of work.
     *
     * @var DocumentManagerInterface
     */
    private $manager;

    /**
     * The current event manager.
     *
     * @var EventManager
     */
    private $evm;

    /**
     * @var Hydrator
     */
    private $hydrator;

    public function __construct(DocumentManagerInterface $manager, Hydrator $hydrator)
    {
        $this->manager = $manager;
        $this->hydrator = $hydrator;

        $this->evm = $manager->getEventManager();
    }

    /**
     * Clears the unit of work.
     * If document class is given, only documents of that class will be detached.
     *
     * @param null|string $documentClass
     */
    public function clear(?string $documentClass = null): void
    {
        if (null === $documentClass) {
            $this->identityMap =
            $this->objects =
            $this->documentStates =
            $this->documentPersisters =
            $this->originalDocumentData = [];
        } else {
            throw new \Exception('Not implemented yet.');
        }

        if ($this->evm->hasListeners(Events::onClear)) {
            $this->evm->dispatchEvent(Events::onClear, new Events\OnClearEventArgs($this->manager, $documentClass));
        }
    }

    /**
     * Gets the document persister for a given document class.
     *
     * @param string $documentClass
     *
     * @return DocumentPersister
     */
    public function getDocumentPersister(string $documentClass): DocumentPersister
    {
        if (isset($this->documentPersisters[$documentClass])) {
            return $this->documentPersisters[$documentClass];
        }

        return $this->documentPersisters[$documentClass] = new DocumentPersister($this->manager, $this->manager->getClassMetadata($documentClass), $this->hydrator);
    }

    /**
     * Searches for a document in the identity map and returns it if found.
     * Returns null otherwise.
     *
     * @param mixed            $id
     * @param DocumentMetadata $class
     *
     * @return object|null
     */
    public function tryGetById($id, DocumentMetadata $class)
    {
        return $this->identityMap[$class->name][(string) $id] ?? null;
    }

    /**
     * Checks if a document is attached to this unit of work.
     *
     * @param $object
     *
     * @return bool
     */
    public function isInIdentityMap($object): bool
    {
        $oid = spl_object_hash($object);
        if (! isset($this->objects[$oid])) {
            return false;
        }

        $metadata = $this->manager->getClassMetadata($object);
        $id = $metadata->getIdentifierValues($object);

        if (empty($id)) {
            return false;
        }

        return isset($this->identityMap[$metadata->name][$id]);
    }

    /**
     * Gets the document state.
     *
     * @param $document
     * @param int|null $assume
     *
     * @return int
     */
    public function getDocumentState($document, ?int $assume = null)
    {
        $oid = spl_object_hash($document);

        if (isset($this->documentStates[$oid])) {
            return $this->documentStates[$oid];
        }

        if (null !== $assume) {
            return $assume;
        }

        // State here can only be NEW or DETACHED, as MANAGED and REMOVED states are known.
        $class = $this->manager->getClassMetadata(get_class($document));
        $id = $class->getSingleIdentifier($document);

        if (empty($id)) {
            return self::STATE_NEW;
        }

        if ($this->tryGetById($id, $class)) {
            return self::STATE_DETACHED;
        }

        $persister = $this->getDocumentPersister($class->name);
        if ($persister->exists(['_id' => $id])) {
            return self::STATE_DETACHED;
        }

        return self::STATE_NEW;
    }

    /**
     * Detaches a document from the unit of work.
     *
     * @param $object
     */
    public function detach($object): void
    {
        $this->doDetach($object);
    }

    /**
     * INTERNAL:
     * Hydrates a document.
     *
     * @param Document   $document the elastica document containing the original data
     * @param object     $result   the resulting document object
     * @param array|null $fields   specify the fields for partial hydration
     *
     * @throws InvalidIdentifierException
     */
    public function createDocument(Document $document, &$result, ?array $fields = null)
    {
        $metadata = $this->manager->getClassMetadata($result);
        if (! $result instanceof $metadata->name) {
            throw new \InvalidArgumentException('Unexpected object type for hydration');
        }

        $typeManager = $this->manager->getTypeManager();
        $documentData = $document->getData();

        foreach ($metadata->attributesMetadata as $fieldMetadata) {
            if (! $fieldMetadata instanceof FieldMetadata) {
                continue;
            }

            if ($fieldMetadata->identifier) {
                $fieldMetadata->setValue($result, $document->getId());
                continue;
            }

            if ($fieldMetadata->indexName) {
                $fieldMetadata->setValue($result, $document->getIndex());
                continue;
            }

            if ($fieldMetadata->typeName) {
                $fieldMetadata->setValue($result, $document->getType());
                continue;
            }
        }

        foreach ($documentData as $key => $value) {
            /** @var FieldMetadata $field */
            $field = $metadata->getField($key);
            if (null === $field) {
                continue;
            }

            if (null !== $fields && ! in_array($field->getName(), $fields)) {
                continue;
            }

            $fieldType = $typeManager->getType($field->type);
            $value = $fieldType->toPHP($value, $field->options);

            $field->setValue($result, $value);
        }

        $this->originalDocumentData[spl_object_hash($result)] = $documentData;
        $this->addToIdentityMap($result);
    }

    /**
     * Adds a document to the identity map.
     * The identifier MUST be set before trying to add the document or
     * this method will throw an InvalidIdentifierException.
     *
     * @param $object
     *
     * @throws InvalidIdentifierException
     */
    private function addToIdentityMap($object)
    {
        $oid = spl_object_hash($object);
        if (isset($this->objects[$oid])) {
            return;
        }

        $metadata = $this->manager->getClassMetadata($object);
        $id = $metadata->getSingleIdentifier($object);

        if (empty($id)) {
            throw new InvalidIdentifierException('Documents must have an identifier in order to be added to the identity map.');
        }

        $this->objects[$oid] = $object;
        $this->identityMap[$metadata->name][$id] = $object;
        $this->documentStates[$oid] = self::STATE_MANAGED;
    }

    /**
     * Removes an object from identity map
     *
     * @param $object
     *
     * @throws InvalidIdentifierException
     */
    private function removeFromIdentityMap($object)
    {
        $class = $this->manager->getClassMetadata($object);
        $id = $class->getSingleIdentifier($object);

        if (empty($id)) {
            throw new InvalidIdentifierException('Documents must have an identifier in order to be added to the identity map.');
        }

        unset($this->identityMap[$class->name][$id]);
    }

    private function doDetach($object, array &$visited = [])
    {
        $oid = spl_object_hash($object);
        if (isset($visited[$oid])) {
            return;
        }

        $visited[$oid] = true;

        $state = $this->getDocumentState($object, self::STATE_DETACHED);
        if (self::STATE_MANAGED !== $state) {
            return;
        }

        unset(
            $this->documentStates[$oid],
            $this->objects[$oid],
            $this->originalDocumentData[$oid]
        );

        $this->removeFromIdentityMap($object);
        $this->cascadeDetach($object, $visited);
    }

    private function cascadeDetach($object, $visited)
    {
        // @todo
    }
}

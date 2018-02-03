<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica;

use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\ObjectManagerAware;
use Elastica\Document;
use Fazland\ODM\Elastica\Events\LifecycleEventManager;
use Fazland\ODM\Elastica\Events\PreFlushEventArgs;
use Fazland\ODM\Elastica\Exception\InvalidIdentifierException;
use Fazland\ODM\Elastica\Id\AssignedIdGenerator;
use Fazland\ODM\Elastica\Id\GeneratorInterface;
use Fazland\ODM\Elastica\Id\IdentityGenerator;
use Fazland\ODM\Elastica\Internal\CommitOrderCalculator;
use Fazland\ODM\Elastica\Metadata\DocumentMetadata;
use Fazland\ODM\Elastica\Metadata\FieldMetadata;
use Fazland\ODM\Elastica\Persister\DocumentPersister;
use ProxyManager\Proxy\LazyLoadingInterface;

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
     * The current lifecycle event manager.
     *
     * @var LifecycleEventManager
     */
    private $lifecycleEventManager;

    /**
     * Map of pending document deletions.
     *
     * @var array
     */
    private $documentDeletions = [];

    /**
     * Map of pending document insertions.
     *
     * @var array
     */
    private $documentInsertions = [];

    /**
     * Map of pending document updates.
     *
     * @var array
     */
    private $documentUpdates = [];

    /**
     * Map of read-only document.
     * Keys are the object hash.
     *
     * @var array
     */
    private $readOnlyObjects = [];

    /**
     * Maps of document change sets.
     * Keys are the object hash.
     *
     * @var array
     */
    private $documentChangeSets;

    public function __construct(DocumentManagerInterface $manager)
    {
        $this->manager = $manager;
        $this->evm = $manager->getEventManager();
        $this->lifecycleEventManager = new LifecycleEventManager($this, $this->evm);
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
            $this->documentDeletions =
            $this->documentUpdates =
            $this->documentChangeSets =
            $this->readOnlyObjects =
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

        return $this->documentPersisters[$documentClass] = new DocumentPersister($this->manager, $this->manager->getClassMetadata($documentClass));
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

        $class = $this->manager->getClassMetadata(get_class($object));
        $id = $class->getSingleIdentifier($object);

        if (empty($id)) {
            return false;
        }

        return isset($this->identityMap[$class->name][$id]);
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
     * Commits all the operations pending in this unit of work.
     */
    public function commit()
    {
        if ($this->evm->hasListeners(Events::preFlush)) {
            $this->evm->dispatchEvent(Events::preFlush, new PreFlushEventArgs($this->manager));
        }

        $this->computeChangeSets();

        if (! (
            $this->documentInsertions ||
            $this->documentDeletions ||
            $this->documentUpdates ||
            $this->documentChangeSets
        )) {
            // Nothing to do.
            $this->dispatchOnFlush();
            $this->dispatchPostFlush();

            return;
        }

        $classOrder = $this->getCommitOrder();

        $this->dispatchOnFlush();

        foreach ($classOrder as $className) {
            $this->executeInserts($className);
            $this->executeUpdates($className);
            $this->executeDeletions($className);

            $this->getDocumentPersister($className)->refreshCollection();
        }

        $this->dispatchPostFlush();
        $this->postCommitCleanup();
    }

    public function computeChangeSets(): void
    {
        $this->computeScheduledInsertsChangeSets();

        foreach ($this->identityMap as $className => $documents) {
            $class = $this->manager->getClassMetadata($className);

            // @todo readonly

            foreach ($documents as $document) {
                if ($document instanceof LazyLoadingInterface && ! $document->isProxyInitialized()) {
                    continue;
                }

                $oid = spl_object_hash($document);
                if (! isset($this->documentInsertions[$oid]) && ! isset($this->documentDeletions[$oid]) && isset($this->documentStates[$oid])) {
                    $this->computeChangeSet($class, $document);
                }
            }
        }
    }

    public function recomputeSingleDocumentChangeset($document)
    {
        // @todo
    }

    /**
     * Retrieve the computed changeset for a given document.
     *
     * @param object $document
     *
     * @return array
     */
    public function &getDocumentChangeSet($document)
    {
        $oid = spl_object_hash($document);
        $data = [];

        if (! isset($this->documentChangeSets[$oid])) {
            return $data;
        }

        return $this->documentChangeSets[$oid];
    }

    /**
     * Detaches a document from the unit of work.
     *
     * @param $object
     */
    public function detach($object): void
    {
        $visited = [];
        $this->doDetach($object, $visited);
    }

    /**
     * Persists a document as part of this unit of work.
     *
     * @param $object
     */
    public function persist($object): void
    {
        $visited = [];
        $this->doPersist($object, $visited);
    }

    /**
     * Removes a document as part of this unit of work.
     *
     * @param $object
     */
    public function remove($object): void
    {
        $visited = [];
        $this->doRemove($object, $visited);
    }

    /**
     * Merges the given document with the managed one.
     *
     * @param $object
     *
     * @return object the managed copy of the document
     */
    public function merge($object)
    {
        $visited = [];

        return $this->doMerge($object, $visited);
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
        $class = $this->manager->getClassMetadata(get_class($result));
        $typeManager = $this->manager->getTypeManager();
        $documentData = $document->getData();

        // inject ObjectManager upon refresh.
        if ($result instanceof ObjectManagerAware) {
            $result->injectObjectManager($this->manager, $class);
        }

        foreach ($class->attributesMetadata as $fieldMetadata) {
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

        foreach ($documentData as $key => &$value) {
            /** @var FieldMetadata $field */
            $field = $class->getField($key);
            if (null === $field) {
                continue;
            }

            if (null !== $fields && ! in_array($field->getName(), $fields)) {
                continue;
            }

            $fieldType = $typeManager->getType($field->type);

            if ($field->multiple) {
                $value = array_map(function ($item) use ($fieldType, $field) {
                    return $fieldType->toPHP($item, $field->options);
                }, (array) $value);
            } else {
                $value = $fieldType->toPHP($value, $field->options);
            }

            $field->setValue($result, $value);
        }

        $this->originalDocumentData[spl_object_hash($result)] = $documentData;
        $this->addToIdentityMap($result);
    }

    /**
     * INTERNAL:
     * Gets an id generator for the given type.
     *
     * @param int $generatorType
     *
     * @return GeneratorInterface
     *
     * @internal
     */
    public function getIdGenerator(int $generatorType): GeneratorInterface
    {
        static $generators = [];
        if (isset($generators[$generatorType])) {
            return $generators[$generatorType];
        }

        switch ($generatorType) {
            case DocumentMetadata::GENERATOR_TYPE_NONE:
                $generator = new AssignedIdGenerator();
                break;

            case DocumentMetadata::GENERATOR_TYPE_AUTO:
                $generator = new IdentityGenerator();
                break;

            default:
                throw new \InvalidArgumentException('Unknown id generator type '.$generatorType);
        }

        return $generators[$generatorType] = $generator;
    }

    /**
     * Computes the changes that happened to a single document.
     *
     * @param DocumentMetadata $class
     * @param $document
     */
    private function computeChangeSet(DocumentMetadata $class, $document): void
    {
        $oid = spl_object_hash($document);
        if (isset($this->readOnlyObjects[$oid])) {
            return;
        }

        $actualData = [];
        foreach ($class->attributesMetadata as $field) {
            if (! $field instanceof FieldMetadata || ! $field->isStored()) {
                continue;
            }

            $actualData[$field->fieldName] = $field->getValue($document);
        }

        if (! isset($this->originalDocumentData[$oid])) {
            // Entity is either NEW or MANAGED but not yet fully persisted.
            $this->originalDocumentData[$oid] = $actualData;

            $changeSet = [];
            foreach ($actualData as $field => $value) {
                $changeSet[$field] = [null, $value];
            }

            $this->documentChangeSets[$oid] = $changeSet;
        } else {
            // Document is MANAGED
            $originalData = $this->originalDocumentData[$oid];
            $changeSet = [];

            if (! $document instanceof LazyLoadingInterface || $document->isProxyInitialized()) {
                foreach ($actualData as $propName => $actualValue) {
                    // skip field, its a partially omitted one!
                    if (! array_key_exists($propName, $originalData)) {
                        continue;
                    }

                    $orgValue = $originalData[$propName];

                    // skip if value haven't changed
                    if ($orgValue === $actualValue) {
                        continue;
                    }

                    $changeSet[$propName] = [$orgValue, $actualValue];
                }
            }

            if ($changeSet) {
                $this->documentChangeSets[$oid] = $changeSet;
                $this->originalDocumentData[$oid] = $actualData;
                $this->documentUpdates[$oid] = $document;
            }
        }
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

        $class = $this->manager->getClassMetadata(get_class($object));
        $id = $class->getSingleIdentifier($object);

        if (empty($id)) {
            throw new InvalidIdentifierException('Documents must have an identifier in order to be added to the identity map.');
        }

        $this->objects[$oid] = $object;
        $this->identityMap[$class->name][$id] = $object;
        $this->documentStates[$oid] = self::STATE_MANAGED;
    }

    /**
     * Removes an object from identity map.
     *
     * @param $object
     *
     * @throws InvalidIdentifierException
     */
    private function removeFromIdentityMap($object)
    {
        $class = $this->manager->getClassMetadata(get_class($object));
        $id = $class->getSingleIdentifier($object);

        if (empty($id)) {
            throw new InvalidIdentifierException('Documents must have an identifier in order to be added to the identity map.');
        }

        unset($this->identityMap[$class->name][$id]);
    }

    /**
     * Executes a persist operation.
     *
     * @param object $object
     * @param array  $visited
     *
     * @throws \InvalidArgumentException if document state is equal to NEW
     */
    private function doPersist($object, array &$visited): void
    {
        $oid = spl_object_hash($object);
        if (isset($visited[$oid])) {
            return;
        }

        $visited[$oid] = true;
        $class = $this->manager->getClassMetadata(get_class($object));

        $documentState = $this->getDocumentState($object, self::STATE_NEW);
        switch ($documentState) {
            case self::STATE_MANAGED:
                break;

            case self::STATE_NEW:
                $this->persistNew($class, $object);
                break;

            case self::STATE_REMOVED:
                unset($this->documentDeletions[$oid]);
                $this->documentStates[$oid] = self::STATE_MANAGED;

                break;
        }

        $this->cascadePersist($object, $visited);
    }

    /**
     * Executes a remove operation.
     *
     * @param object $object
     * @param array  $visited
     *
     * @throws \InvalidArgumentException if document state is equal to NEW
     */
    private function doRemove($object, array &$visited): void
    {
        $oid = spl_object_hash($object);
        if (isset($visited[$oid])) {
            return;
        }

        $visited[$oid] = true;

        // Cascade first to avoid problems with proxy initializing out of the identity map.
        $this->cascadeRemove($object, $visited);

        $class = $this->manager->getClassMetadata(get_class($object));
        $documentState = $this->getDocumentState($object);

        switch ($documentState) {
            case self::STATE_NEW:
            case self::STATE_REMOVED:
                // Nothing to do.
                break;

            case self::STATE_MANAGED:
                $this->lifecycleEventManager->preRemove($class, $object);
                $this->scheduleForDeletion($object);
                break;

            case self::STATE_DETACHED:
                throw new \InvalidArgumentException('Detached document cannot be removed');
            default:
                throw new \InvalidArgumentException('Unexpected document state '.$documentState);
        }
    }

    /**
     * Executes a merge operation on a document.
     *
     * @param object $object
     * @param array  $visited
     *
     * @return object the managed copy of the document
     *
     * @throws \InvalidArgumentException if document state is equal to NEW
     */
    private function doMerge($object, array &$visited)
    {
        $oid = spl_object_hash($object);

        if (isset($visited[$oid])) {
            return $visited[$oid];
        }

        $visited[$oid] = $object;

        /** @var DocumentMetadata $class */
        $class = $this->manager->getClassMetadata(get_class($object));
        $managedCopy = $object;

        if (self::STATE_MANAGED !== $this->getDocumentState($object, self::STATE_DETACHED)) {
            $this->manager->initializeObject($object);

            $id = $class->getSingleIdentifier($object);
            $managedCopy = null;

            if (null !== $id) {
                $managedCopy = $this->manager->find($class->name, $id);

                if (null !== $managedCopy && self::STATE_REMOVED === $this->getDocumentState($managedCopy)) {
                    throw new \InvalidArgumentException('Removed document detected during merge.');
                }

                $this->manager->initializeObject($managedCopy);
            }

            if (null === $managedCopy) {
                $managedCopy = $this->newInstance($class);
                if (null !== $id) {
                    $class->setIdentifierValue($managedCopy, $id);
                }

                $this->persistNew($class, $managedCopy);
            }

            foreach ($class->getReflectionClass()->getProperties() as $property) {
                $name = $property->name;
                $property->setAccessible(true);

                if (! isset($class->associationMappings[$name])) {
                    if (! $class->isIdentifier($name)) {
                        $property->setValue($managedCopy, $property->getValue($object));
                    }
                } else {
                    // @todo
                }
            }
        }

        $visited[spl_object_hash($managedCopy)] = $managedCopy;
        $this->cascadeMerge($object, $managedCopy, $visited);

        return $managedCopy;
    }

    /**
     * Execute detach operation.
     *
     * @param $object
     * @param array $visited
     *
     * @throws InvalidIdentifierException
     */
    private function doDetach($object, array &$visited)
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

    private function persistNew(DocumentMetadata $class, $object)
    {
        $this->lifecycleEventManager->prePersist($class, $object);
        $oid = spl_object_hash($object);

        if ($class->identifier) {
            $idGenerator = $this->getIdGenerator($class->idGeneratorType);
            if (! $idGenerator->isPostInsertGenerator()) {
                $id = $idGenerator->generate($this->manager, $object);
                $class->setIdentifierValue($object, $id);
            }
        } else {
            // @todo Embedded
        }

        $this->documentStates[$oid] = self::STATE_MANAGED;
        $this->scheduleForInsert($object);
    }

    private function cascadeDetach($object, $visited)
    {
        // @todo
    }

    private function cascadeMerge($object, $managedCopy, $visited)
    {
        // @todo
    }

    private function cascadeRemove($object, $visited)
    {
        // @todo
    }

    private function cascadePersist($object, $visited)
    {
        // @todo
    }

    /**
     * Schedule a document for insertion.
     * If the document already has an identifier it will be added to the identity map.
     *
     * @param object $object
     *
     * @throws InvalidIdentifierException
     */
    private function scheduleForInsert($object)
    {
        $oid = spl_object_hash($object);
        $class = $this->manager->getClassMetadata(get_class($object));

        $this->documentInsertions[$oid] = $object;

        if (null !== $class->getSingleIdentifier($object)) {
            $this->addToIdentityMap($object);
        }
    }

    /**
     * Schedule a document for deletion.
     *
     * @param object $object
     *
     * @throws InvalidIdentifierException
     */
    private function scheduleForDeletion($object)
    {
        $oid = spl_object_hash($object);
        if (isset($this->documentInsertions[$oid])) {
            if ($this->isInIdentityMap($object)) {
                $this->removeFromIdentityMap($object);
            }

            unset($this->documentInsertions[$oid], $this->documentStates[$oid]);

            return;
        }

        if (! $this->isInIdentityMap($object)) {
            return;
        }

        $this->removeFromIdentityMap($object);
        unset($this->documentUpdates[$oid]);

        $this->documentDeletions[$oid] = $object;
        $this->documentStates[$oid] = self::STATE_REMOVED;
    }

    private function dispatchOnFlush()
    {
        // @todo
    }

    private function dispatchPostFlush()
    {
        // @todo
    }

    private function executeInserts(string $className)
    {
        foreach ($this->documentInsertions as $oid => $document) {
            /** @var DocumentMetadata $class */
            $class = $this->manager->getClassMetadata(get_class($document));
            if ($className !== $class->name) {
                continue;
            }

            $persister = $this->getDocumentPersister($class->name);

            $postInsertId = $persister->insert($document);

            if (null !== $postInsertId) {
                $id = $postInsertId->getId();
                $oid = spl_object_hash($document);

                $class->setIdentifierValue($document, $id);
                $this->documentStates[$oid] = self::STATE_MANAGED;

                $this->addToIdentityMap($document);
            }

            $this->lifecycleEventManager->postPersist($class, $document);
        }
    }

    private function executeUpdates(string $className)
    {
        foreach ($this->documentUpdates as $oid => $document) {
            /** @var DocumentMetadata $class */
            $class = $this->manager->getClassMetadata(get_class($document));
            if ($className !== $class->name) {
                continue;
            }

            $this->lifecycleEventManager->preUpdate($class, $document);

            $persister = $this->getDocumentPersister(get_class($document));
            if (! empty($this->documentChangeSets[$oid])) {
                $persister->update($document);
            }

            unset($this->documentUpdates[$oid], $this->documentChangeSets[$oid]);
            $this->lifecycleEventManager->postUpdate($class, $document);
        }
    }

    private function executeDeletions(string $className)
    {
        foreach ($this->documentDeletions as $oid => $document) {
            /** @var DocumentMetadata $class */
            $class = $this->manager->getClassMetadata(get_class($document));
            if ($className !== $class->name) {
                continue;
            }

            $persister = $this->getDocumentPersister($class->name);
            $persister->delete($document);

            unset($this->documentDeletions[$oid], $this->originalDocumentData[$oid], $this->documentStates[$oid]);

            if (DocumentMetadata::GENERATOR_TYPE_NONE !== $class->idGeneratorType) {
                $class->setIdentifierValue($document, null);
            }

            $this->lifecycleEventManager->postRemove($class, $document);
        }
    }

    private function postCommitCleanup()
    {
        $this->documentDeletions =
        $this->documentInsertions =
        $this->documentUpdates =
        $this->documentChangeSets = [];
    }

    private function computeScheduledInsertsChangeSets()
    {
        foreach ($this->documentInsertions as $document) {
            $class = $this->manager->getClassMetadata(get_class($document));
            $this->computeChangeSet($class, $document);
        }
    }

    /**
     * Creates a new instance of given class and inject object manager if needed.
     *
     * @param DocumentMetadata $class
     *
     * @return object
     */
    private function newInstance(DocumentMetadata $class)
    {
        $document = $class->newInstance();

        // inject ObjectManager upon refresh.
        if ($document instanceof ObjectManagerAware) {
            $document->injectObjectManager($this->manager, $class);
        }

        return $document;
    }

    /**
     * Calculates the commit order, based on associations
     * on document metadata.
     *
     * @return array
     */
    private function getCommitOrder(): array
    {
        static $calculator = null;
        if (null === $calculator) {
            $calculator = new CommitOrderCalculator();
        }

        $objects = array_merge($this->documentInsertions, $this->documentUpdates, $this->documentDeletions);

        $classes = [];
        foreach ($objects as $object) {
            $metadata = $this->manager->getClassMetadata(get_class($object));

            $calculator->addClass($metadata);
            $classes[$metadata->getName()] = $metadata;
        }

        $order = $calculator->getOrder(array_keys($classes));

        return array_map(function (array $element) {
            return $element[0]->getClassName();
        }, $order);
    }
}

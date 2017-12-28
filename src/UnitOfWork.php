<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica;

use Elastica\Document;
use Fazland\ODM\Elastica\Exception\InvalidIdentifierException;
use Fazland\ODM\Elastica\Metadata\FieldMetadata;

final class UnitOfWork
{
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
     * @var DocumentManagerInterface
     */
    private $manager;

    public function __construct(DocumentManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Searches for a document in the identity map and returns it if found.
     * Returns null otherwise.
     *
     * @param string $className
     * @param mixed $id
     *
     * @return object|null
     */
    public function tryGetById(string $className, $id)
    {
        $metadata = $this->manager->getClassMetadata($className);

        return $this->identityMap[$metadata->name][(string) $id] ?? null;
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
    public function addToIdentityMap($object)
    {
        $oid = spl_object_hash($object);
        if (isset($this->objects[$oid])) {
            return;
        }

        $this->objects[$oid] = $object;

        $metadata = $this->manager->getClassMetadata($object);
        $id = $metadata->getIdentifierValues($object);

        if (empty($id)) {
            throw new InvalidIdentifierException('Documents must have an identifier in order to be added to the identity map.');
        }

        $this->identityMap[$metadata->name][implode(' ', $id)] = $object;
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
     * INTERNAL:
     * Hydrates a document
     *
     * @param Document $document The elastica document containing the original data.
     * @param object $result The resulting document object.
     * @param array|null $fields Specify the fields for partial hydration.
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

        $this->originalDocumentData[ spl_object_hash($result) ] = $documentData;
        $this->addToIdentityMap($result);
    }
}

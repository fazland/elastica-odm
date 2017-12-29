<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Metadata;

use Doctrine\Common\Persistence\Mapping\ClassMetadata as ClassMetadataInterface;
use Doctrine\Instantiator\Instantiator;
use Kcs\Metadata\ClassMetadata;
use Kcs\Metadata\MetadataInterface;

final class DocumentMetadata extends ClassMetadata implements ClassMetadataInterface
{
    const GENERATOR_TYPE_NONE = 0;
    const GENERATOR_TYPE_AUTO = 1;

    /**
     * The elastica type name.
     *
     * @var string
     */
    public $typeName;

    /**
     * The identifier field name.
     *
     * @var FieldMetadata
     */
    public $identifier;

    /**
     * Identifier generator type.
     *
     * @var int
     */
    public $idGeneratorType;

    /**
     * The fully-qualified class name of the custom repository class.
     * Optional.
     *
     * @var string|null
     */
    public $customRepositoryClassName;

    /**
     * The instantiator used to build new object instances.
     *
     * @var Instantiator
     */
    private $instantiator;

    public function __construct(\ReflectionClass $class)
    {
        parent::__construct($class);

        $this->instantiator = new Instantiator();
    }

    /**
     * @inheritdoc
     *
     * @param self $metadata
     */
    public function merge(MetadataInterface $metadata): void
    {
        parent::merge($metadata);

        $this->customRepositoryClassName = $this->customRepositoryClassName ?? $metadata->customRepositoryClassName;
        $this->typeName = $this->typeName ?? $metadata->typeName;
        $this->identifier = $this->identifier ?? $metadata->identifier;
        $this->idGeneratorType = $this->idGeneratorType ?? $metadata->idGeneratorType;
    }

    /**
     * Returns a new object instance.
     *
     * @return object
     */
    public function newInstance()
    {
        return $this->instantiator->instantiate($this->name);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): array
    {
        return [$this->identifier->fieldName];
    }

    /**
     * {@inheritdoc}
     */
    public function isIdentifier($fieldName): bool
    {
        return $this->identifier->fieldName === $fieldName;
    }

    /**
     * {@inheritdoc}
     */
    public function hasField($fieldName): bool
    {
        return isset($this->attributesMetadata[$fieldName]) && $this->attributesMetadata[$fieldName]->field;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAssociation($fieldName): bool
    {
        // TODO: Implement hasAssociation() method.
    }

    /**
     * {@inheritdoc}
     */
    public function isSingleValuedAssociation($fieldName): bool
    {
        // TODO: Implement isSingleValuedAssociation() method.
    }

    /**
     * {@inheritdoc}
     */
    public function isCollectionValuedAssociation($fieldName): bool
    {
        // TODO: Implement isCollectionValuedAssociation() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldNames(): array
    {
        return array_keys($this->attributesMetadata);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierFieldNames(): array
    {
        return [$this->identifier->fieldName];
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociationNames(): array
    {
        // TODO: Implement getAssociationNames() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeOfField($fieldName): string
    {
        return $this->attributesMetadata[$fieldName]->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociationTargetClass($assocName): string
    {
        // TODO: Implement getAssociationTargetClass() method.
    }

    /**
     * {@inheritdoc}
     */
    public function isAssociationInverseSide($assocName): bool
    {
        // TODO: Implement isAssociationInverseSide() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociationMappedByTargetField($assocName): string
    {
        // TODO: Implement getAssociationMappedByTargetField() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierValues($object): array
    {
        $class = $this->name;
        if (! $object instanceof $class) {
            throw new \InvalidArgumentException('Unexpected object class');
        }

        if (null === $this->identifier) {
            return [];
        }

        $property = $this->identifier->getReflection();

        return [$this->identifier->fieldName => $property->getValue($object)];
    }

    public function getSingleIdentifierFieldName()
    {
        return $this->identifier->fieldName;
    }

    public function getSingleIdentifier($object)
    {
        $id = $this->getIdentifierValues($object);
        if (empty($id)) {
            return null;
        }

        return reset($id);
    }

    public function setIdentifierValue($object, $value): void
    {
        $this->identifier->setValue($object, $value);
    }

    public function getField(string $fieldName): ?FieldMetadata
    {
        foreach ($this->attributesMetadata as $metadata) {
            if (! $metadata instanceof FieldMetadata) {
                continue;
            }

            if ($metadata->fieldName === $fieldName) {
                return $metadata;
            }
        }

        return null;
    }
}

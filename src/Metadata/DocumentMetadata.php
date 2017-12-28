<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Metadata;

use Doctrine\Common\Persistence\Mapping\ClassMetadata as ClassMetadataInterface;
use Kcs\Metadata\ClassMetadata;

final class DocumentMetadata extends ClassMetadata implements ClassMetadataInterface
{
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
     * The fully-qualified class name of the custom repository class.
     * (Optional).
     *
     * @var string|null
     */
    public $customRepositoryClassName;

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
    }

    /**
     * Registers a custom repository class.
     *
     * @param string $repositoryClassName the class name of the custom mapper
     */
    public function setCustomRepositoryClass(string $repositoryClassName): void
    {
        $this->customRepositoryClassName = $repositoryClassName;
    }
}

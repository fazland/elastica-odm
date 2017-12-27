<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Metadata;

use Kcs\Metadata\ClassMetadata;

final class DocumentMetadata extends ClassMetadata implements \Doctrine\Common\Persistence\Mapping\ClassMetadata
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
     * @inheritDoc
     */
    public function getIdentifier()
    {
        return [$this->identifier->fieldName];
    }

    /**
     * @inheritDoc
     */
    public function isIdentifier($fieldName)
    {
        return $this->identifier->fieldName === $fieldName;
    }

    /**
     * @inheritDoc
     */
    public function hasField($fieldName)
    {
        return isset($this->attributesMetadata[$fieldName]) && $this->attributesMetadata[$fieldName]->field;
    }

    /**
     * @inheritDoc
     */
    public function hasAssociation($fieldName)
    {
        // TODO: Implement hasAssociation() method.
    }

    /**
     * @inheritDoc
     */
    public function isSingleValuedAssociation($fieldName)
    {
        // TODO: Implement isSingleValuedAssociation() method.
    }

    /**
     * @inheritDoc
     */
    public function isCollectionValuedAssociation($fieldName)
    {
        // TODO: Implement isCollectionValuedAssociation() method.
    }

    /**
     * @inheritDoc
     */
    public function getFieldNames()
    {
        return array_keys($this->attributesMetadata);
    }

    /**
     * @inheritDoc
     */
    public function getIdentifierFieldNames()
    {
        return [$this->identifier->fieldName];
    }

    /**
     * @inheritDoc
     */
    public function getAssociationNames()
    {
        // TODO: Implement getAssociationNames() method.
    }

    /**
     * @inheritDoc
     */
    public function getTypeOfField($fieldName)
    {
        return $this->attributesMetadata[$fieldName]->type;
    }

    /**
     * @inheritDoc
     */
    public function getAssociationTargetClass($assocName)
    {
        // TODO: Implement getAssociationTargetClass() method.
    }

    /**
     * @inheritDoc
     */
    public function isAssociationInverseSide($assocName)
    {
        // TODO: Implement isAssociationInverseSide() method.
    }

    /**
     * @inheritDoc
     */
    public function getAssociationMappedByTargetField($assocName)
    {
        // TODO: Implement getAssociationMappedByTargetField() method.
    }

    /**
     * @inheritDoc
     */
    public function getIdentifierValues($object)
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

    public function getField(string $fieldName)
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
}

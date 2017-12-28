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
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return [$this->identifier->fieldName];
    }

    /**
     * {@inheritdoc}
     */
    public function isIdentifier($fieldName)
    {
        return $this->identifier->fieldName === $fieldName;
    }

    /**
     * {@inheritdoc}
     */
    public function hasField($fieldName)
    {
        return isset($this->attributesMetadata[$fieldName]) && $this->attributesMetadata[$fieldName]->field;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAssociation($fieldName)
    {
        // TODO: Implement hasAssociation() method.
    }

    /**
     * {@inheritdoc}
     */
    public function isSingleValuedAssociation($fieldName)
    {
        // TODO: Implement isSingleValuedAssociation() method.
    }

    /**
     * {@inheritdoc}
     */
    public function isCollectionValuedAssociation($fieldName)
    {
        // TODO: Implement isCollectionValuedAssociation() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldNames()
    {
        return array_keys($this->attributesMetadata);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierFieldNames()
    {
        return [$this->identifier->fieldName];
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociationNames()
    {
        // TODO: Implement getAssociationNames() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeOfField($fieldName)
    {
        return $this->attributesMetadata[$fieldName]->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociationTargetClass($assocName)
    {
        // TODO: Implement getAssociationTargetClass() method.
    }

    /**
     * {@inheritdoc}
     */
    public function isAssociationInverseSide($assocName)
    {
        // TODO: Implement isAssociationInverseSide() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociationMappedByTargetField($assocName)
    {
        // TODO: Implement getAssociationMappedByTargetField() method.
    }

    /**
     * {@inheritdoc}
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

    public function getSingleIdentifier($object)
    {
        $id = $this->getIdentifierValues($object);
        if (empty($id)) {
            return null;
        }

        return reset($id);
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

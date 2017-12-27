<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Metadata;

use Kcs\Metadata\PropertyMetadata;

class FieldMetadata extends PropertyMetadata
{
    /**
     * @var bool
     */
    public $identifier;

    /**
     * @var bool
     */
    public $field = false;

    /**
     * @var bool
     */
    public $indexName;

    /**
     * @var bool
     */
    public $typeName;

    /**
     * @var string
     */
    public $fieldName;

    /**
     * @var string
     */
    public $type;

    /**
     * @var array
     */
    public $options = [];

    /**
     * @var \ReflectionProperty
     */
    private $reflectionProperty;

    public function getReflection(): \ReflectionProperty
    {
        if (null === $this->reflectionProperty) {
            $this->reflectionProperty = new \ReflectionProperty($this->class, $this->name);
            $this->reflectionProperty->setAccessible(true);
        }

        return $this->reflectionProperty;
    }

    public function setValue($object, $value)
    {
        $reflection = $this->getReflection();
        $reflection->setValue($object, $value);
    }
}

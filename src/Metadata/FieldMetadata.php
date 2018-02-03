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
     * @var bool
     */
    public $multiple = false;

    /**
     * @var array
     */
    public $options = [];

    /**
     * @var bool
     */
    public $lazy = false;

    /**
     * @var DocumentMetadata
     */
    public $documentMetadata;

    /**
     * @var \ReflectionProperty
     */
    private $reflectionProperty;

    public function __construct(DocumentMetadata $class, string $name)
    {
        $this->documentMetadata = $class;

        parent::__construct($class->name, $name);
    }

    public function getReflection(): \ReflectionProperty
    {
        if (null === $this->reflectionProperty) {
            $this->reflectionProperty = new \ReflectionProperty($this->class, $this->name);
            $this->reflectionProperty->setAccessible(true);
        }

        return $this->reflectionProperty;
    }

    public function getValue($object)
    {
        return $this->getReflection()->getValue($object);
    }

    public function setValue($object, $value)
    {
        $reflection = $this->getReflection();
        $reflection->setValue($object, $value);
    }

    public function isStored(): bool
    {
        return ! (
            $this->identifier ||
            $this->indexName ||
            $this->typeName
        );
    }
}

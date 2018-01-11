<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tools;

use Elastica\Type\Mapping;
use Fazland\ODM\Elastica\Metadata\DocumentMetadata;
use Fazland\ODM\Elastica\Metadata\FieldMetadata;
use Fazland\ODM\Elastica\Type\TypeManager;

class MappingGenerator
{
    /**
     * @var TypeManager
     */
    private $typeManager;

    public function __construct(TypeManager $typeManager)
    {
        $this->typeManager = $typeManager;
    }

    public function getMapping(DocumentMetadata $class): Mapping
    {
        $properties = [];

        foreach ($class->getAttributesMetadata() as $field) {
            if (! $field instanceof FieldMetadata) {
                continue;
            }

            if (null === $field->type) {
                continue;
            }

            $type = $this->typeManager->getType($field->type);
            $properties[$field->fieldName] = $type->getMappingDeclaration($field->options);
        }

        return Mapping::create($properties);
    }
}

<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Metadata;

use Kcs\Metadata\Loader\AnnotationProcessorLoader;
use Kcs\Metadata\MetadataInterface;

class Loader extends AnnotationProcessorLoader
{
    /**
     * @inheritDoc
     */
    protected function createPropertyMetadata(\ReflectionProperty $reflectionProperty): MetadataInterface
    {
        return new FieldMetadata($reflectionProperty->class, $reflectionProperty->name);
    }
}

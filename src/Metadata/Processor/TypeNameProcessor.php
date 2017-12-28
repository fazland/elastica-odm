<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Metadata\Processor;

use Fazland\ODM\Elastica\Annotation\TypeName;
use Fazland\ODM\Elastica\Metadata\FieldMetadata;
use Kcs\Metadata\Loader\Processor\ProcessorInterface;
use Kcs\Metadata\MetadataInterface;

class TypeNameProcessor implements ProcessorInterface
{
    /**
     * {@inheritdoc}
     *
     * @param FieldMetadata $metadata
     * @param TypeName      $subject
     */
    public function process(MetadataInterface $metadata, $subject): void
    {
        $metadata->typeName = true;
    }
}

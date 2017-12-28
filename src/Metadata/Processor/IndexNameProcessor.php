<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Metadata\Processor;

use Fazland\ODM\Elastica\Annotation\IndexName;
use Fazland\ODM\Elastica\Metadata\FieldMetadata;
use Kcs\Metadata\Loader\Processor\ProcessorInterface;
use Kcs\Metadata\MetadataInterface;

class IndexNameProcessor implements ProcessorInterface
{
    /**
     * {@inheritdoc}
     *
     * @param FieldMetadata $metadata
     * @param IndexName     $subject
     */
    public function process(MetadataInterface $metadata, $subject): void
    {
        $metadata->indexName = true;
    }
}

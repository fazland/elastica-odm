<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Metadata\Processor;

use Fazland\ODM\Elastica\Annotation\DocumentId;
use Fazland\ODM\Elastica\Metadata\FieldMetadata;
use Kcs\Metadata\Loader\Processor\ProcessorInterface;
use Kcs\Metadata\MetadataInterface;

class DocumentIdProcessor implements ProcessorInterface
{
    /**
     * @inheritDoc
     *
     * @param FieldMetadata $metadata
     * @param DocumentId $subject
     */
    public function process(MetadataInterface $metadata, $subject): void
    {
        $metadata->identifier = true;
    }
}

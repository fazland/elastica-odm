<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Metadata\Processor;

use Fazland\ODM\Elastica\Annotation\DocumentId;
use Fazland\ODM\Elastica\Metadata\DocumentMetadata;
use Fazland\ODM\Elastica\Metadata\FieldMetadata;
use Kcs\Metadata\Loader\Processor\Annotation\Processor;
use Kcs\Metadata\Loader\Processor\ProcessorInterface;
use Kcs\Metadata\MetadataInterface;

/**
 * @Processor(annotation=DocumentId::class)
 */
class DocumentIdProcessor implements ProcessorInterface
{
    /**
     * {@inheritdoc}
     *
     * @param FieldMetadata $metadata
     * @param DocumentId    $subject
     */
    public function process(MetadataInterface $metadata, $subject): void
    {
        $metadata->identifier = true;

        if ('auto' === $subject->strategy) {
            $metadata->documentMetadata->idGeneratorType = DocumentMetadata::GENERATOR_TYPE_AUTO;
        } else {
            $metadata->documentMetadata->idGeneratorType = DocumentMetadata::GENERATOR_TYPE_NONE;
        }
    }
}

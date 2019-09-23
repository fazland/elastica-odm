<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Metadata\Processor;

use Fazland\ODM\Elastica\Annotation\Document;
use Fazland\ODM\Elastica\Metadata\DocumentMetadata;
use Kcs\Metadata\Loader\Processor\Annotation\Processor;
use Kcs\Metadata\Loader\Processor\ProcessorInterface;
use Kcs\Metadata\MetadataInterface;

/**
 * @Processor(annotation=Document::class)
 */
class DocumentProcessor implements ProcessorInterface
{
    /**
     * {@inheritdoc}
     *
     * @param DocumentMetadata $metadata
     * @param Document         $subject
     */
    public function process(MetadataInterface $metadata, $subject): void
    {
        $metadata->document = true;
        $metadata->collectionName = $subject->type;
        $metadata->customRepositoryClassName = $subject->repositoryClass;
    }
}

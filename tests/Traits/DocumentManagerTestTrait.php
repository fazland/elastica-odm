<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Traits;

use Doctrine\Common\Annotations\AnnotationReader;
use Fazland\ODM\Elastica\Annotation;
use Fazland\ODM\Elastica\Builder;
use Fazland\ODM\Elastica\DocumentManagerInterface;
use Fazland\ODM\Elastica\Metadata\Loader\AnnotationLoader;
use Fazland\ODM\Elastica\Metadata\Processor;
use Kcs\Metadata\Loader\Processor\ProcessorFactory;

trait DocumentManagerTestTrait
{
    private static function createDocumentManager(): DocumentManagerInterface
    {
        $processorFactory = new ProcessorFactory();
        $processorFactory->registerProcessor(Annotation\Document::class, Processor\DocumentProcessor::class);
        $processorFactory->registerProcessor(Annotation\DocumentId::class, Processor\DocumentIdProcessor::class);
        $processorFactory->registerProcessor(Annotation\IndexName::class, Processor\IndexNameProcessor::class);
        $processorFactory->registerProcessor(Annotation\TypeName::class, Processor\TypeNameProcessor::class);
        $processorFactory->registerProcessor(Annotation\Field::class, Processor\FieldProcessor::class);

        $loader = new AnnotationLoader($processorFactory, __DIR__.'/../Fixtures/Document');
        $loader->setReader(new AnnotationReader());

        return Builder::create()
            ->addMetadataLoader($loader)
            ->build();
    }
}
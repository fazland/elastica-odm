<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Traits;

use Doctrine\Common\Annotations\AnnotationReader;
use Fazland\ODM\Elastica\Builder;
use Fazland\ODM\Elastica\DocumentManagerInterface;
use Fazland\ODM\Elastica\Metadata\Loader\AnnotationLoader;
use Kcs\Metadata\Loader\Processor\ProcessorFactory;

trait DocumentManagerTestTrait
{
    private static function createDocumentManager(): DocumentManagerInterface
    {
        $processorFactory = new ProcessorFactory();
        $processorFactory->registerProcessors(__DIR__.'/../../src/Metadata/Processor');

        $loader = new AnnotationLoader($processorFactory, __DIR__.'/../Fixtures/Document');
        $loader->setReader(new AnnotationReader());

        $builder = Builder::create()->addMetadataLoader($loader);

        if ($endpoint = \getenv('ES_ENDPOINT')) {
            $builder->setConnectionUrl($endpoint);
        }

        return $builder->build();
    }
}

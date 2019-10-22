<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Traits;

use Elastica\Cluster\Settings;
use Elastica\Type\Mapping;
use Elasticsearch\Endpoints;
use Fazland\ODM\Elastica\DocumentManagerInterface;

trait FixturesTestTrait
{
    private static function resetFixtures(DocumentManagerInterface $dm): void
    {
        $database = $dm->getDatabase();
        $connection = $database->getConnection();
        (new Settings($connection))->set([
            'persistent' => [
                'action.auto_create_index' => '-foo_index_no_auto_create,+*',
            ],
        ]);

        $connection->requestEndpoint((new Endpoints\Indices\Delete())->setIndex('*'));
        $connection->requestEndpoint((new Endpoints\Indices\Create())->setIndex('foo_index'));
        $connection->requestEndpoint((new Endpoints\Indices\Create())->setIndex('foo_lazy_index'));
        $connection->requestEndpoint((new Endpoints\Indices\Create())->setIndex('foo_with_aliases_index_foo_alias'));

        $fooIndex = $connection->getIndex('foo_index');
        $fooType = $fooIndex->getType('foo_type');
        Mapping::create([
            'stringField' => ['type' => 'text'],
        ])
            ->setType($fooType)
            ->send()
        ;

        $connection->requestEndpoint(
            (new Endpoints\Index())
                ->setType($fooType->getName())
                ->setIndex($fooIndex->getName())
                ->setBody([
                    'stringField' => 'foobar',
                ])
        );

        $connection->requestEndpoint(
            (new Endpoints\Index())
                ->setType($fooType->getName())
                ->setIndex($fooIndex->getName())
                ->setBody([
                    'stringField' => 'barbaz',
                ])
        );

        $connection->requestEndpoint(
            (new Endpoints\Index())
                ->setType($fooType->getName())
                ->setIndex($fooIndex->getName())
                ->setID('foo_test_document')
                ->setBody([
                    'stringField' => 'bazbaz',
                ])
        );

        $connection->requestEndpoint((new Endpoints\Indices\Refresh())->setIndex($fooIndex->getName()));

        $fooIndex = $connection->getIndex('foo_lazy_index');
        $fooType = $fooIndex->getType('foo_type');
        Mapping::create([
            'stringField' => ['type' => 'text'],
        ])
            ->setType($fooType)
            ->send()
        ;

        $connection->requestEndpoint(
            (new Endpoints\Index())
                ->setType($fooType->getName())
                ->setIndex($fooIndex->getName())
                ->setBody([
                    'stringField' => 'foobar',
                    'lazyField' => 'lazyFoo',
                ])
        );

        $connection->requestEndpoint(
            (new Endpoints\Index())
                ->setType($fooType->getName())
                ->setIndex($fooIndex->getName())
                ->setBody([
                    'stringField' => 'barbaz',
                    'lazyField' => 'lazyBar',
                ])
        );

        $connection->requestEndpoint(
            (new Endpoints\Index())
                ->setType($fooType->getName())
                ->setIndex($fooIndex->getName())
                ->setID('foo_test_document')
                ->setBody([
                    'stringField' => 'bazbaz',
                    'lazyField' => 'lazyBaz',
                ])
        );

        $connection->requestEndpoint((new Endpoints\Indices\Refresh())->setIndex($fooIndex->getName()));

        $fooIndex = $connection->getIndex('foo_with_aliases_index_foo_alias');
        $fooType = $fooIndex->getType('foo_type');
        Mapping::create([
            'stringField' => ['type' => 'text'],
        ])
               ->setType($fooType)
               ->send()
        ;

        $connection->requestEndpoint((new Endpoints\Indices\Refresh())->setIndex($fooIndex->getName()));
        $connection->requestEndpoint(
            (new Endpoints\Indices\Alias\Put())
                ->setName('foo_with_aliases_index')
                ->setIndex('foo_with_aliases_index_foo_alias')
        );
    }
}

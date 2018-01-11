<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Traits;

use Elastica\Type\Mapping;
use Elasticsearch\Endpoints\Index;
use Elasticsearch\Endpoints\Indices\Create;
use Elasticsearch\Endpoints\Indices\Delete;
use Elasticsearch\Endpoints\Indices\Refresh;
use Fazland\ODM\Elastica\DocumentManagerInterface;

trait FixturesTestTrait
{
    private static function resetFixtures(DocumentManagerInterface $dm)
    {
        $database = $dm->getDatabase();
        $connection = $database->getConnection();
        $connection->requestEndpoint((new Delete())->setIndex('*'));
        $connection->requestEndpoint((new Create())->setIndex('foo_index'));

        $fooIndex = $connection->getIndex('foo_index');
        $fooType = $fooIndex->getType('foo_type');
        Mapping::create([
            'stringField' => ['type' => 'text'],
        ])
            ->setType($fooType)
            ->send();

        $connection->requestEndpoint(
            (new Index())
                ->setType($fooType->getName())
                ->setIndex($fooIndex->getName())
                ->setBody([
                    'stringField' => 'foobar',
                ])
        );
        $connection->requestEndpoint(
            (new Index())
                ->setType($fooType->getName())
                ->setIndex($fooIndex->getName())
                ->setBody([
                    'stringField' => 'barbaz',
                ])
        );

        $connection->requestEndpoint((new Refresh())->setIndex($fooIndex->getName()));
    }
}

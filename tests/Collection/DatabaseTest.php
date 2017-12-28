<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Collection;

use Elastica\Client;
use Elastica\Index;
use Fazland\ODM\Elastica\Collection\Database;
use Fazland\ODM\Elastica\DocumentManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class DatabaseTest extends TestCase
{
    public function testGetIndexWithAliasShouldReturnTheSameIndex(): void
    {
        /** @var Client|ObjectProphecy $client */
        $client = $this->prophesize(Client::class);
        /** @var Client|ObjectProphecy $documentManager */
        $documentManager = $this->prophesize(DocumentManagerInterface::class);

        $database = new Database($client->reveal(), $documentManager->reveal());

        $index = 'index';
        $alias1 = 'alias1';
        $alias2 = 'alias2';

        $database->addAlias($alias1, $index);
        $database->addAlias($alias2, $index);

        $client->getIndex($index)->willReturn($this->prophesize(Index::class))->shouldBeCalledTimes(2);

        $database->getIndex($alias1);
        $database->getIndex($alias2);
    }
}

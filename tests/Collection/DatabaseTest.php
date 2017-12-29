<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Collection;

use Elastica\Client;
use Elastica\Index;
use Fazland\ODM\Elastica\Collection\CollectionInterface;
use Fazland\ODM\Elastica\Collection\Database;
use Fazland\ODM\Elastica\DocumentManagerInterface;
use Fazland\ODM\Elastica\Metadata\DocumentMetadata;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Cache\CacheItemPoolInterface;

class DatabaseTest extends TestCase
{
    /**
     * @var Client|ObjectProphecy
     */
    private $client;

    /**
     * @var DocumentManagerInterface|ObjectProphecy
     */
    private $documentManager;

    /**
     * @var Database
     */
    private $database;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->client = $this->prophesize(Client::class);
        $this->documentManager = $this->prophesize(DocumentManagerInterface::class);

        $this->database = new Database($this->client->reveal(), $this->documentManager->reveal());
    }

    public function testGetIndexWithAliasShouldReturnTheSameIndex(): void
    {
        $index = 'index';
        $alias1 = 'alias1';
        $alias2 = 'alias2';

        $this->database->addAlias($alias1, $index);
        $this->database->addAlias($alias2, $index);

        $this->client->getIndex($index)->willReturn($this->prophesize(Index::class))->shouldBeCalledTimes(2);

        $this->database->getIndex($alias1);
        $this->database->getIndex($alias2);
    }

    public function testGetCollectionShouldReturnACollectionWithTheDatabaseCache(): void
    {
        $class = new DocumentMetadata(new \ReflectionClass(\stdClass::class));
        $class->name = 'document_name';
        $class->typeName = 'type_name';

        $index = $this->prophesize(Index::class);

        $this->client->getIndex($class->typeName)->willReturn($index);

        $resultCache = $this->prophesize(CacheItemPoolInterface::class);

        $this->database->setResultCache($resultCache->reveal());

        $collection = $this->database->getCollection($class);
        $this->assertInstanceOf(CollectionInterface::class, $collection);

        $this->assertEquals($resultCache->reveal(), $collection->getResultCache());
    }

    public function testGetCollectionCalledMoreThanOnceShouldRetrieveTheSameCollectionInstance(): void
    {
        $class = new DocumentMetadata(new \ReflectionClass(\stdClass::class));
        $class->name = 'document_name';
        $class->typeName = 'type_name';

        $index = $this->prophesize(Index::class);

        $this->client->getIndex($class->typeName)->willReturn($index);

        $collection = $this->database->getCollection($class);

        $collection2 = $this->database->getCollection($class);

        $this->assertEquals($collection, $collection2);
    }
}

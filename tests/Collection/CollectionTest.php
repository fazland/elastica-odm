<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Collection;

use Elastica\Query;
use Elastica\Response;
use Elastica\ResultSet;
use Elastica\Scroll as ElasticaScroll;
use Elastica\Search as ElasticaSearch;
use Elastica\Type;
use Elasticsearch\Endpoints;
use Fazland\ODM\Elastica\Collection\Collection;
use Fazland\ODM\Elastica\Collection\CollectionInterface;
use Fazland\ODM\Elastica\DocumentManagerInterface;
use Fazland\ODM\Elastica\Tests\Fixtures\Document\Foo;
use Fazland\ODM\Elastica\Tests\Traits\DocumentManagerTestTrait;
use Fazland\ODM\Elastica\Tests\Traits\FixturesTestTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class CollectionTest extends TestCase
{
    use DocumentManagerTestTrait;
    use FixturesTestTrait;

    /**
     * @var Type|ObjectProphecy
     */
    private $searchable;

    /**
     * @var Query|ObjectProphecy
     */
    private $query;

    /**
     * @var string
     */
    private $documentClass;

    /**
     * @var CollectionInterface
     */
    private $collection;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->searchable = $this->prophesize(Type::class);
        $this->query = $this->prophesize(Query::class);
        $this->documentClass = \stdClass::class;

        $this->collection = new Collection(
            $this->documentClass,
            $this->searchable->reveal()
        );
    }

    public static function setUpBeforeClass()
    {
        $dm = self::createDocumentManager();
        self::resetFixtures($dm);
    }

    public function testScrollShouldSetDefaultSortingIfNotSet(): void
    {
        $search = $this->prophesize(ElasticaSearch::class);
        $this->searchable->createSearch($this->query)->shouldBeCalled()->willReturn($search);

        $this->query->hasParam('sort')->willReturn(false);
        $this->query->setSort(['_doc'])->shouldBeCalled();

        $expiryTime = '1m';

        $search->scroll($expiryTime)->willReturn($this->prophesize(ElasticaScroll::class));
        $this->collection->scroll($this->query->reveal(), $expiryTime);
    }

    /**
     * @group functional
     */
    public function testScroll()
    {
        $dm = self::createDocumentManager();

        $collection = $dm->getCollection(Foo::class);
        $scroll = iterator_to_array($collection->scroll(new Query()), false);
        $resultSet = $scroll[0];

        $this->assertCount(2, $resultSet);
        $this->assertArrayHasKey('stringField', $resultSet[0]->getSource());
    }

    public function testSearchShouldExecuteTheQuery(): void
    {
        $this->searchable->search($this->query)
            ->shouldBeCalled()
            ->willReturn($this->prophesize(ResultSet::class))
        ;

        $this->collection->search($this->query->reveal());
    }

    public function testCreateSearchShouldWork(): void
    {
        $documentManager = $this->prophesize(DocumentManagerInterface::class);
        $search = $this->collection->createSearch($documentManager->reveal(), $this->query->reveal());

        $this->assertEquals($this->query->reveal(), $search->getQuery());
    }

    public function testCountShouldUseSearchableInterfaceCount(): void
    {
        $this->searchable->count($this->query)->shouldBeCalled()->willReturn(10);
        $this->collection->count($this->query->reveal());
    }

    public function testRefreshShouldCallRefreshEndpoint(): void
    {
        $this->searchable->requestEndpoint(new Endpoints\Indices\Refresh())->shouldBeCalled();
        $this->collection->refresh();
    }

    public function testCreateShouldFireIndexRequest(): void
    {
        $endpoint = new Endpoints\Index();
        $endpoint->setParams(['op_type' => 'create']);
        $endpoint->setID('test_id');
        $endpoint->setBody(['field' => 'value']);

        $this->searchable->requestEndpoint($endpoint)
            ->willReturn(new Response(['_id' => 'test_id'], 200))
            ->shouldBeCalled();

        $this->collection->create('test_id', ['field' => 'value']);
    }

    public function testCreateShouldSetLastInsertId(): void
    {
        $endpoint = new Endpoints\Index();
        $endpoint->setBody(['field' => 'value']);

        $this->searchable->requestEndpoint($endpoint)
            ->willReturn(new Response(['_id' => 'foo_id'], 200))
            ->shouldBeCalled();

        $this->collection->create(null, ['field' => 'value']);
        $this->assertEquals('foo_id', $this->collection->getLastInsertedId());
    }

    /**
     * @expectedException \Fazland\ODM\Elastica\Exception\RuntimeException
     */
    public function testCreateShouldThrowIfResponseIsNotOk(): void
    {
        $endpoint = new Endpoints\Index();
        $endpoint->setBody(['field' => 'value']);

        $this->searchable->requestEndpoint($endpoint)
            ->willReturn(new Response(['_id' => 'foo_id'], 409))
            ->shouldBeCalled();

        $this->collection->create(null, ['field' => 'value']);
    }

    /**
     * @group functional
     */
    public function testCreate()
    {
        $dm = self::createDocumentManager();

        $collection = $dm->getCollection(Foo::class);
        $response = $collection->create('test_index_create', ['stringField' => 'value']);

        $this->assertTrue($response->isOk());
        $this->assertEquals('test_index_create', $collection->getLastInsertedId());
    }

    /**
     * @group functional
     * @expectedException \Fazland\ODM\Elastica\Exception\RuntimeException
     */
    public function testCreateShouldThrowOnDuplicates()
    {
        $dm = self::createDocumentManager();

        $collection = $dm->getCollection(Foo::class);
        $response = $collection->create('test_index_create_duplicate', ['stringField' => 'value']);
        $collection->refresh();

        $this->assertTrue($response->isOk());

        $collection->create('test_index_create_duplicate', ['stringField' => 'value']);
    }

    /**
     * @group functional
     */
    public function testCreateWithAutoGeneratedId()
    {
        $dm = self::createDocumentManager();

        $collection = $dm->getCollection(Foo::class);
        $response = $collection->create(null, ['stringField' => 'value']);

        $this->assertTrue($response->isOk());
        $this->assertNotNull($collection->getLastInsertedId());
    }
}

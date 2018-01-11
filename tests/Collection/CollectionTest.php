<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Collection;

use Elastica\Query;
use Elastica\ResultSet;
use Elastica\Scroll as ElasticaScroll;
use Elastica\Search as ElasticaSearch;
use Elastica\SearchableInterface;
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
     * @var SearchableInterface|ObjectProphecy
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
        $this->searchable = $this->prophesize(SearchableInterface::class);
        $this->query = $this->prophesize(Query::class);
        $this->documentClass = \stdClass::class;

        $this->collection = new Collection(
            $this->documentClass,
            $this->searchable->reveal()
        );
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

    /**
     * @group functional
     */
    public function testScroll()
    {
        $dm = $this->createDocumentManager();
        $this->resetFixtures($dm);

        $collection = $dm->getCollection(Foo::class);
        $scroll = iterator_to_array($collection->scroll(new Query()), false);
        $resultSet = $scroll[0];

        $this->assertCount(2, $resultSet);
        $this->assertArrayHasKey('stringField', $resultSet[0]->getSource());
    }
}

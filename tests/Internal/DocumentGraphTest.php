<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Internal;

use Fazland\ODM\Elastica\Internal\DocumentGraph;
use Fazland\ODM\Elastica\Internal\DocumentGraphEdge;
use Fazland\ODM\Elastica\Internal\DocumentGraphNode;
use Fazland\ODM\Elastica\Tests\Fixtures\Document\Foo;
use PHPUnit\Framework\TestCase;

class DocumentGraphTest extends TestCase
{
    /**
     * @var DocumentGraph
     */
    private $graph;

    protected function setUp()
    {
        $this->graph = new DocumentGraph();
    }

    public function testGetNodesReturnsEmptyArrayOnEmptyGraph()
    {
        self::assertEquals([], $this->graph->getNodes());
    }

    public function testAddNode()
    {
        $this->graph->addNode(\stdClass::class);

        self::assertEquals([
            \stdClass::class => new DocumentGraphNode(\stdClass::class),
        ], $this->graph->getNodes());
    }

    public function testConnect()
    {
        $this->graph->addNode(Foo::class);
        $this->graph->addNode(\stdClass::class);

        $this->graph->connect(Foo::class, \stdClass::class);

        $fooNode = new DocumentGraphNode(Foo::class);
        $stdNode = new DocumentGraphNode(\stdClass::class);

        $edge = new DocumentGraphEdge($fooNode, $stdNode);

        $fooNode->addOutEdge($edge);
        $stdNode->addInEdge($edge);

        self::assertEquals([
            Foo::class => $fooNode,
            \stdClass::class => $stdNode,
        ], $this->graph->getNodes());
    }

    public function testIterator()
    {
        $this->graph->addNode(Foo::class);
        $this->graph->addNode(\stdClass::class);

        $this->graph->connect(Foo::class, \stdClass::class);

        $fooNode = new DocumentGraphNode(Foo::class);
        $stdNode = new DocumentGraphNode(\stdClass::class);

        $edge = new DocumentGraphEdge($fooNode, $stdNode);

        $fooNode->addOutEdge($edge);
        $stdNode->addInEdge($edge);

        self::assertEquals([
            [$stdNode, 2],
            [$fooNode, 1],
        ], \iterator_to_array($this->graph));
    }
}

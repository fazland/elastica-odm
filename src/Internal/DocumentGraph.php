<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Internal;

/**
 * Represents a document dependency graph.
 * Used to calculate commit order.
 *
 * @internal
 */
final class DocumentGraph implements \IteratorAggregate
{
    /**
     * @var DocumentGraphNode[]
     */
    private $nodes;

    public function __construct()
    {
        $this->nodes = [];
    }

    /**
     * Adds a node to the dependency graph.
     *
     * @param string $className
     */
    public function addNode(string $className): void
    {
        if (isset($this->nodes[$className])) {
            return;
        }

        $this->nodes[$className] = new DocumentGraphNode($className);
    }

    /**
     * Connects two nodes.
     *
     * @param string $source
     * @param string $destination
     */
    public function connect(string $source, string $destination): void
    {
        $outNode = $this->nodes[$source];
        $inNode = $this->nodes[$destination];

        $edge = new DocumentGraphEdge($outNode, $inNode);

        $outNode->addOutEdge($edge);
        $inNode->addInEdge($edge);
    }

    /**
     * Gets the nodes in the graph.
     *
     * @return DocumentGraphNode[]
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Generator
    {
        $elements = \iterator_to_array((function () {
            $traverse = static function (DocumentGraphNode $node, int $depth) use (&$traverse) {
                yield [$node, $depth];

                foreach ($node as $edge) {
                    yield from $traverse($edge->getDestination(), $depth + 1);
                }
            };

            foreach ($this->nodes as $node) {
                yield from $traverse($node, 1);
            }
        })(), false);

        \uasort($elements, static function (array $a, array $b) {
            return $b[1] <=> $a[1];
        });

        $visited = [];
        foreach ($elements as $element) {
            $name = $element[0]->getClassName();
            if (isset($visited[$name])) {
                continue;
            }

            $visited[$name] = true;
            yield $element;
        }
    }
}

<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Internal;

/**
 * Represents a document dependency graph node.
 * Count will return the number of nodes which depend on this (in edges).
 * Traverse will iterate through the out edges.
 *
 * @internal
 */
final class DocumentGraphNode implements \Countable, \IteratorAggregate
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var DocumentGraphEdge[]
     */
    private $inEdges;

    /**
     * @var DocumentGraphEdge[]
     */
    private $outEdges;

    public function __construct(string $className)
    {
        $this->className = $className;

        $this->inEdges = [];
        $this->outEdges = [];
    }

    /**
     * Adds an in edge to this node.
     *
     * @param DocumentGraphEdge $edge
     */
    public function addInEdge(DocumentGraphEdge $edge): void
    {
        $this->inEdges[$edge->getSource()->className] = $edge;
    }

    /**
     * Adds an out edge from this node.
     *
     * @param DocumentGraphEdge $edge
     */
    public function addOutEdge(DocumentGraphEdge $edge): void
    {
        $this->outEdges[$edge->getDestination()->className] = $edge;
    }

    /**
     * Gets the document class name.
     *
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * {@inheritdoc}
     *
     * Traverse the graph through the out edges.
     *
     * @return \Generator|DocumentGraphEdge[]
     */
    public function getIterator(): \Generator
    {
        yield from $this->outEdges;
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return \count($this->inEdges);
    }
}

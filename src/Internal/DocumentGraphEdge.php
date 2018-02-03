<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Internal;

/**
 * Represents a document dependency graph edge.
 *
 * @internal
 */
final class DocumentGraphEdge
{
    /**
     * @var DocumentGraphNode
     */
    private $source;

    /**
     * @var DocumentGraphNode
     */
    private $destination;

    public function __construct(DocumentGraphNode $source, DocumentGraphNode $destination)
    {
        $this->source = $source;
        $this->destination = $destination;
    }

    /**
     * @return DocumentGraphNode
     */
    public function getSource(): DocumentGraphNode
    {
        return $this->source;
    }

    /**
     * @return DocumentGraphNode
     */
    public function getDestination(): DocumentGraphNode
    {
        return $this->destination;
    }
}

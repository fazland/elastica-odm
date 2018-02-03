<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Internal;

use Fazland\ODM\Elastica\Metadata\DocumentMetadata;

final class CommitOrderCalculator
{
    /**
     * @var DocumentGraph
     */
    private $graph;

    public function __construct()
    {
        $this->graph = new DocumentGraph();
    }

    /**
     * Adds a document class to the dependency graph
     * and evaluates its associations.
     *
     * @param DocumentMetadata $metadata
     */
    public function addClass(DocumentMetadata $metadata): void
    {
        $this->graph->addNode($metadata->name);

        $assocNames = $metadata->getAssociationNames();
        foreach ($assocNames as $assocName) {
            $targetClass = $metadata->getAssociationTargetClass($assocName);
            $this->graph->addNode($targetClass);
            $this->graph->connect($metadata->name, $targetClass);
        }
    }

    /**
     * Gets the commit order set.
     *
     * @param array $classNames
     *
     * @return array
     */
    public function getOrder(array $classNames): array
    {
        $elements = array_filter(iterator_to_array($this->graph), function ($element) use ($classNames): bool {
            list($node) = $element;

            return in_array($node->getClassName(), $classNames);
        });

        return array_reverse($elements, false);
    }
}

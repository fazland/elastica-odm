<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Id;

use Fazland\ODM\Elastica\DocumentManagerInterface;
use Fazland\ODM\Elastica\Exception\InvalidIdentifierException;

interface GeneratorInterface
{
    /**
     * Generates a document identifier.
     *
     * @param DocumentManagerInterface $dm
     * @param $document
     *
     * @return mixed
     *
     * @throws InvalidIdentifierException if id cannot be generated or invalid
     */
    public function generate(DocumentManagerInterface $dm, $document);

    /**
     * Whether this generator must be called after the insert operation.
     *
     * @return bool
     */
    public function isPostInsertGenerator(): bool;
}

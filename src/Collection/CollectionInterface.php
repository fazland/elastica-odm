<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Collection;

use Elastica\Query;
use Elastica\Response;
use Elastica\ResultSet;
use Elastica\Scroll;
use Elastica\Type\Mapping;
use Fazland\ODM\Elastica\DocumentManagerInterface;
use Fazland\ODM\Elastica\Search\Search;

interface CollectionInterface
{
    /**
     * Gets the name of the collection (could be index/type or just index name
     * in case the ES version does not support types any more).
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Executes a search.
     *
     * @param Query $query
     *
     * @return ResultSet
     */
    public function search(Query $query): ResultSet;

    /**
     * Executes a scroll search.
     *
     * @param Query  $query
     * @param string $expiryTime
     *
     * @return Scroll
     */
    public function scroll(Query $query, string $expiryTime = '1m'): Scroll;

    /**
     * Creates a search object.
     *
     * @param DocumentManagerInterface $documentManager
     * @param Query                    $query
     *
     * @return Search
     */
    public function createSearch(DocumentManagerInterface $documentManager, Query $query): Search;

    /**
     * Counts document matching query.
     *
     * @param Query $query
     *
     * @return int
     */
    public function count(Query $query): int;

    /**
     * Executes a refresh operation on the index.
     */
    public function refresh(): void;

    /**
     * Request the index of a document.
     *
     * @param string|null $id
     * @param array       $body
     *
     * @return Response
     */
    public function create(?string $id, array $body): Response;

    /**
     * Updates a document.
     *
     * @param string $id
     * @param array  $body
     * @param string $script
     */
    public function update(string $id, array $body, string $script = ''): void;

    /**
     * Request the deletion of a document.
     *
     * @param string $id
     */
    public function delete(string $id): void;

    /**
     * Request the deletion of a set of document, matched by the given query.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-delete-by-query.html
     *      for the limitations of this method and the available parameters.
     *
     * @param Query\AbstractQuery $query
     * @param array $params
     */
    public function deleteByQuery(Query\AbstractQuery $query, array $params = []): void;

    /**
     * Returns the last inserted identifier as string.
     *
     * @return string
     */
    public function getLastInsertedId(): ?string;

    /**
     * Updates the collection mapping.
     *
     * @param Mapping $mapping
     */
    public function updateMapping(Mapping $mapping): void;

    /**
     * Drops the entire collection.
     */
    public function drop(): void;
}

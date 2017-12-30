<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Collection;

use Elastica\Query;
use Elastica\Response;
use Elastica\ResultSet;
use Elastica\Scroll;
use Fazland\ODM\Elastica\DocumentManagerInterface;
use Fazland\ODM\Elastica\Search\Search;
use Psr\Cache\CacheItemPoolInterface;

interface CollectionInterface
{
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
     * Gets the configured result cache pool.
     *
     * @return null|CacheItemPoolInterface
     */
    public function getResultCache(): ?CacheItemPoolInterface;

    /**
     * Sets the result cache pool.
     *
     * @param null|CacheItemPoolInterface $resultCache
     */
    public function setResultCache(?CacheItemPoolInterface $resultCache): void;

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
     * @param null|string $id
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
     */
    public function update(string $id, array $body): void;

    /**
     * Request the deletion of a document.
     *
     * @param string $id
     */
    public function delete(string $id): void;

    /**
     * Returns the last inserted identifier as string.
     *
     * @return string
     */
    public function getLastInsertedId(): ?string;
}

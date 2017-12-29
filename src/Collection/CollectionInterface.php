<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Collection;

use Elastica\Query;
use Elastica\ResultSet;
use Elastica\Scroll;
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
     * @param Query $query
     *
     * @return Search
     */
    public function createSearch(Query $query): Search;

    /**
     * Counts document matching query.
     *
     * @param Query $query
     *
     * @return int
     */
    public function count(Query $query): int;
}

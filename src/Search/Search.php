<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Search;

use Elastica\Query;

class Search
{
    /**
     * @var string
     */
    private $documentClass;

    /**
     * @var string|Query
     */
    private $query = '';

    /**
     * @var SearchCacheProfile
     */
    private $cacheProfile;

    /**
     * @var Executor
     */
    private $queryExecutor;

    /**
     * @var bool
     */
    private $scroll = false;

    public function __construct(string $documentClass, Executor $queryExecutor)
    {
        $this->documentClass = $documentClass;
        $this->queryExecutor = $queryExecutor;
    }

    /**
     * Gets the document class to retrieve.
     *
     * @return string
     */
    public function getDocumentClass(): string
    {
        return $this->documentClass;
    }

    /**
     * Executes the query.
     *
     * @return array
     */
    public function execute(): array
    {
        return iterator_to_array($this->queryExecutor->execute($this), false);
    }

    /**
     * Sets the search query.
     *
     * @param Query|string $query
     *
     * @return $this|self
     */
    public function setQuery($query): self
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Gets the search query.
     *
     * @return Query|string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param bool $scroll
     *
     * @return $this|self
     */
    public function setScroll(bool $scroll = true): self
    {
        $this->scroll = $scroll;

        return $this;
    }

    /**
     * @return bool
     */
    public function isScroll(): bool
    {
        return $this->scroll;
    }

    /**
     * Instructs the executor to use a result cache.
     *
     * @param string $cacheKey
     * @param int    $ttl
     *
     * @return $this|self
     */
    public function useResultCache(string $cacheKey = null, int $ttl = 0): self
    {
        if (null === $cacheKey) {
            $this->cacheProfile = null;
        } else {
            $this->cacheProfile = new SearchCacheProfile($cacheKey, $ttl);
        }

        return $this;
    }

    /**
     * Gets the cache profile (if set).
     *
     * @return null|SearchCacheProfile
     */
    public function getCacheProfile()
    {
        return $this->cacheProfile;
    }
}

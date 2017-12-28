<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Search;

use Elastica\Query;
use Elastica\ResultSet;
use Fazland\ODM\Elastica\Collection\CollectionInterface;
use Fazland\ODM\Elastica\DocumentManagerInterface;

class Search
{
    /**
     * @var string
     */
    private $documentClass;

    /**
     * @var Query
     */
    private $query;

    /**
     * @var SearchCacheProfile
     */
    private $cacheProfile;

    /**
     * @var bool
     */
    private $scroll;
    /**
     * @var DocumentManagerInterface
     */
    private $documentManager;

    public function __construct(DocumentManagerInterface $documentManager, string $documentClass)
    {
        $this->documentManager = $documentManager;
        $this->documentClass = $documentClass;
        $this->scroll = false;

        $this->setQuery('');
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

    public function execute(): array
    {
        return iterator_to_array($this->doExecute(), false);
    }

    private function doExecute(): \Generator
    {
        $collection = $this->documentManager->getCollection($this->documentClass);
        $hydrator = $this->documentManager->getHydrator();

        if ($this->isScroll()) {
            $scroll = $collection->scroll($this->query);

            foreach ($scroll as $resultSet) {
                yield from $hydrator->hydrateAll($resultSet, $this->documentClass);
            }
        } else {
            yield from $hydrator->hydrateAll($this->executeSearch($collection), $this->documentClass);
        }
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
        $this->query = Query::create($query);

        return $this;
    }

    /**
     * Gets the search query.
     *
     * @return Query
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

    private function executeSearch(CollectionInterface $collection): ResultSet
    {
        if (null !== $this->cacheProfile) {
            return $this->executeCachedSearch($collection);
        }

        return $collection->search($this->query);
    }

    private function executeCachedSearch(CollectionInterface $collection): ResultSet
    {
        $resultCache = $collection->getResultCache();

        if (null !== $resultCache) {
            $item = $resultCache->getItem($this->cacheProfile->getCacheKey());

            if ($item->isHit()) {
                return $item->get();
            }
        }

        $search = clone $this;
        $search->useResultCache(null, 0);
        $resultSet = $search->executeSearch($collection);

        if (isset($item)) {
            $item->set($resultSet);
            $item->expiresAfter($this->cacheProfile->getTtl());
            $resultCache->save($item);
        }

        return $resultSet;
    }
}

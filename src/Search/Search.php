<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Search;

use Elastica\Query;
use Elastica\ResultSet;
use Fazland\ODM\Elastica\DocumentManagerInterface;
use Fazland\ODM\Elastica\Hydrator\HydratorInterface;
use Fazland\ODM\Elastica\Metadata\DocumentMetadata;

class Search implements \IteratorAggregate
{
    /**
     * The target document class.
     *
     * @var string
     */
    private $documentClass;

    /**
     * Hydration mode.
     *
     * @var int
     */
    private $hydrationMode;

    /**
     * Search query.
     *
     * @var Query
     */
    private $query;

    /**
     * Result cache profile.
     *
     * @var SearchCacheProfile
     */
    private $cacheProfile;

    /**
     * Whether to execute a scroll search or not.
     *
     * @var bool
     */
    private $scroll;

    /**
     * Sort fields.
     *
     * @var array
     */
    private $sort;

    /**
     * Max returned results.
     *
     * @var int
     */
    private $limit;

    /**
     * Skipped documents.
     *
     * @var int
     */
    private $offset;

    /**
     * The document manager which this search is bound.
     *
     * @var DocumentManagerInterface
     */
    private $documentManager;

    public function __construct(DocumentManagerInterface $documentManager, string $documentClass)
    {
        $this->documentManager = $documentManager;
        $this->documentClass = $documentClass;
        $this->hydrationMode = HydratorInterface::HYDRATE_OBJECT;
        $this->scroll = false;

        $this->setQuery('');
    }

    /**
     * Gets the current document manager.
     *
     * @return DocumentManagerInterface
     */
    public function getDocumentManager(): DocumentManagerInterface
    {
        return $this->documentManager;
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
     * Gets the query results.
     *
     * @return array
     */
    public function execute(): array
    {
        return iterator_to_array($this->getIterator(), false);
    }

    /**
     * Get the total hits of the current query.
     *
     * @return int
     */
    public function count(): int
    {
        $collection = $this->documentManager->getCollection($this->documentClass);

        return $collection->count($this->query);
    }

    /**
     * Iterate over the query results.
     *
     * @return \Iterator
     */
    public function getIterator(): \Iterator
    {
        $hydrator = $this->documentManager->newHydrator($this->hydrationMode);
        $query = clone $this->query;

        if (! $query->hasParam('_source')) {
            /** @var DocumentMetadata $class */
            $class = $this->documentManager->getClassMetadata($this->documentClass);
            $query->setSource($class->eagerFieldNames);
        }

        if (null !== $this->sort) {
            $query->setSort($this->sort);
        }

        if (null !== $this->limit) {
            $query->setSize($this->limit);
        }

        if (null !== $this->offset) {
            $query->setFrom($this->offset);
        }

        $generator = null !== $this->cacheProfile ? $this->_doExecuteCached($query) : $this->_doExecute($query);
        foreach ($generator as $resultSet) {
            yield from $hydrator->hydrateAll($resultSet, $this->documentClass);
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
     * Sets the sort fields and directions.
     *
     * @param array|string $fieldName
     * @param string       $order
     *
     * @return Search
     */
    public function setSort($fieldName, $order = 'asc'): self
    {
        if (null !== $fieldName) {
            $sort = [];
            $fields = is_array($fieldName) ? $fieldName : [$fieldName => $order];

            foreach ($fields as $fieldName => $order) {
                $sort[] = [$fieldName => $order];
            }
        } else {
            $sort = null;
        }

        $this->sort = $sort;

        return $this;
    }

    /**
     * Gets the sort array.
     *
     * @return array
     */
    public function getSort(): ?array
    {
        return $this->sort;
    }

    /**
     * Sets the query limit.
     *
     * @param int $limit
     *
     * @return $this
     */
    public function setLimit(?int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Gets the max returned documents.
     *
     * @return int
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * Sets the query first result.
     *
     * @param int $offset
     *
     * @return $this
     */
    public function setOffset(?int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Gets the query first result.
     *
     * @return int
     */
    public function getOffset(): ?int
    {
        return $this->offset;
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
        return null === $this->limit && null === $this->offset && $this->scroll;
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

    /**
     * Executes the search action, yield all the result sets.
     *
     * @param Query $query
     *
     * @return \Generator|ResultSet[]
     */
    private function _doExecute(Query $query)
    {
        $collection = $this->documentManager->getCollection($this->documentClass);

        if ($this->isScroll()) {
            $scroll = $collection->scroll($query);

            foreach ($scroll as $resultSet) {
                yield $resultSet;
            }
        } else {
            yield $collection->search($query);
        }
    }

    /**
     * Executes a cached query.
     *
     * @param Query $query
     *
     * @return \Generator|ResultSet[]
     */
    private function _doExecuteCached(Query $query)
    {
        if (null !== $resultCache = $this->documentManager->getResultCache()) {
            $item = $resultCache->getItem($this->cacheProfile->getCacheKey());

            if ($item->isHit()) {
                yield from $item->get();

                return;
            }
        }

        $resultSets = iterator_to_array($this->_doExecute($query));

        if (isset($item)) {
            $item->set($resultSets);
            $item->expiresAfter($this->cacheProfile->getTtl());
            $resultCache->save($item);
        }

        yield from $resultSets;
    }
}

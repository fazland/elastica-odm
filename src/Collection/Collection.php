<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Collection;

use Elastica\Query;
use Elastica\ResultSet;
use Elastica\Scroll;
use Elastica\SearchableInterface;
use Fazland\ODM\Elastica\DocumentManagerInterface;
use Fazland\ODM\Elastica\Search\Search;
use Psr\Cache\CacheItemPoolInterface;

class Collection implements CollectionInterface
{
    /**
     * @var DocumentManagerInterface
     */
    private $documentManager;

    /**
     * @var string
     */
    private $documentClass;

    /**
     * @var SearchableInterface
     */
    private $searchable;

    /**
     * @var CacheItemPoolInterface|null
     */
    private $resultCache;

    public function __construct(DocumentManagerInterface $documentManager, string $documentClass, SearchableInterface $searchable)
    {
        $this->documentManager = $documentManager;
        $this->documentClass = $documentClass;
        $this->searchable = $searchable;
    }

    /**
     * {@inheritdoc}
     */
    public function scroll(Query $query, string $expiryTime = '1m'): Scroll
    {
        // Scroll requests have optimizations that make them faster when the sort order is _doc.
        // Add it to the query if no sort option have been defined.
        if (! $query->hasParam('sort')) {
            $query->setSort(['_doc']);
        }

        return $this->searchable->createSearch($query)->scroll($expiryTime);
    }

    /**
     * {@inheritdoc}
     */
    public function search(Query $query): ResultSet
    {
        return $this->searchable->search($query);
    }

    /**
     * {@inheritdoc}
     */
    public function getResultCache(): ?CacheItemPoolInterface
    {
        return $this->resultCache;
    }

    /**
     * {@inheritdoc}
     */
    public function setResultCache(?CacheItemPoolInterface $resultCache): void
    {
        $this->resultCache = $resultCache;
    }

    /**
     * {@inheritdoc}
     */
    public function createSearch(Query $query): Search
    {
        $search = new Search($this->documentManager, $this->documentClass);
        $search->setQuery($query);

        return $search;
    }

    /**
     * {@inheritdoc}
     */
    public function count(Query $query): int
    {
        return $this->searchable->count($query);
    }
}

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

    public function scroll(Query $query): Scroll
    {
        return $this->searchable->createSearch($query)->scroll();
    }

    public function search(Query $query): ResultSet
    {
        return $this->searchable->search($query);
    }

    public function getResultCache(): ?CacheItemPoolInterface
    {
        return $this->resultCache;
    }

    public function setResultCache(?CacheItemPoolInterface $resultCache): void
    {
        $this->resultCache = $resultCache;
    }

    public function createSearch(): Search
    {
        return new Search($this->documentManager, $this->documentClass);
    }

    public function count(Query $query): int
    {
        return $this->searchable->count($query);
    }
}

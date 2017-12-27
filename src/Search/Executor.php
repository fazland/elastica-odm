<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Search;

use Fazland\ODM\Elastica\DocumentManagerInterface;
use Fazland\ODM\Elastica\Hydrator;
use Elastica\Client;
use Elastica\ResultSet;
use Elastica\Type;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class Executor
{
    /**
     * @var DocumentManagerInterface
     */
    private $manager;

    /**
     * @var Client
     */
    private $elasticSearch;

    /**
     * @var CacheItemPoolInterface
     */
    private $resultCache;

    /**
     * @var Hydrator
     */
    private $hydrator;

    public function __construct(DocumentManagerInterface $manager, Hydrator $hydrator, Client $elasticSearch)
    {
        $this->manager = $manager;
        $this->elasticSearch = $elasticSearch;
        $this->hydrator = $hydrator;
        $this->resultCache = new ArrayAdapter();
    }

    /**
     * Sets the result cache implementation.
     *
     * @param CacheItemPoolInterface $resultCache
     */
    public function setResultCacheImpl(CacheItemPoolInterface $resultCache)
    {
        $this->resultCache = $resultCache;
    }

    /**
     * Fetches the results and hydrates all the documents.
     *
     * @param Search $search
     *
     * @return \Iterator
     */
    public function execute(Search $search): \Iterator
    {
        if ($search->isScroll()) {
            $type = $this->getType($search->getDocumentClass());
            $scroll = $type->createSearch($search->getQuery())->scroll();

            foreach ($scroll as $resultSet) {
                yield from $this->hydrator->hydrateAll($resultSet, $search->getDocumentClass());
            }
        } else {
            yield from $this->hydrator->hydrateAll($this->executeSearch($search), $search->getDocumentClass());
        }
    }

    private function executeSearch(Search $search): ResultSet
    {
        $cacheProfile = $search->getCacheProfile();
        if (null !== $cacheProfile) {
            return $this->executeCachedSearch($search, $cacheProfile);
        }

        $type = $this->getType($search->getDocumentClass());
        return $type->search($search->getQuery());
    }

    private function executeCachedSearch(Search $search, SearchCacheProfile $cacheProfile): ResultSet
    {
        $item = $this->resultCache->getItem($cacheProfile->getCacheKey());
        if ($item->isHit()) {
            return $item->get();
        }

        $search = clone $search;
        $search->useResultCache(null, 0);
        $resultSet = $this->executeSearch($search);

        $item->set($resultSet);
        $item->expiresAfter($cacheProfile->getTtl());
        $this->resultCache->save($item);

        return $resultSet;
    }

    private function getType($className): Type
    {
        $metadata = $this->manager->getClassMetadata($className);
        list($indexName, $typeName) = explode('/', $metadata->typeName, 2);

        return $this->elasticSearch
            ->getIndex($indexName)
            ->getType($typeName);
    }
}

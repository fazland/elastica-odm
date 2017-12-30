<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Collection;

use Elastica\Client;
use Elastica\SearchableInterface;
use Fazland\ODM\Elastica\Metadata\DocumentMetadata;
use Psr\Cache\CacheItemPoolInterface;

class Database implements DatabaseInterface
{
    /**
     * @var Client
     */
    protected $elasticSearch;

    /**
     * @var CacheItemPoolInterface|null
     */
    private $resultCache;

    /**
     * @var CollectionInterface[]
     */
    private $collectionList;

    public function __construct(Client $elasticSearch)
    {
        $this->elasticSearch = $elasticSearch;
        $this->collectionList = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection(DocumentMetadata $class): CollectionInterface
    {
        if (isset($this->collectionList[$class->name])) {
            return $this->collectionList[$class->name];
        }

        $collection = new Collection($class->name, $this->getSearchable($class));
        $collection->setResultCache($this->resultCache);

        return $this->collectionList[$class->name] = $collection;
    }

    public function setResultCache(?CacheItemPoolInterface $resultCache): void
    {
        $this->resultCache = $resultCache;
    }

    protected function getSearchable(DocumentMetadata $class): SearchableInterface
    {
        list($indexName, $typeName) = explode('/', $class->typeName, 2) + [null, null];

        $searchable = $this->elasticSearch->getIndex($indexName);
        if (null !== $typeName) {
            $searchable = $searchable->getType($typeName);
        }

        return $searchable;
    }
}

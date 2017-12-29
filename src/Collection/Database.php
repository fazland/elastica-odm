<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Collection;

use Elastica\Client;
use Elastica\Index;
use Elastica\SearchableInterface;
use Fazland\ODM\Elastica\DocumentManagerInterface;
use Fazland\ODM\Elastica\Metadata\DocumentMetadata;
use Psr\Cache\CacheItemPoolInterface;

class Database implements DatabaseInterface
{
    /**
     * @var string[]
     */
    private $aliases;

    /**
     * @var Client
     */
    private $elasticSearch;

    /**
     * @var DocumentManagerInterface
     */
    private $documentManager;

    /**
     * @var CacheItemPoolInterface|null
     */
    private $resultCache;

    /**
     * @var CollectionInterface[]
     */
    private $collectionList;

    public function __construct(Client $elasticSearch, DocumentManagerInterface $documentManager)
    {
        $this->elasticSearch = $elasticSearch;
        $this->documentManager = $documentManager;
        $this->aliases = [];
        $this->collectionList = [];
    }

    public function addAlias(string $alias, string $indexName): void
    {
        $this->aliases[$alias] = $indexName;
    }

    public function getIndex($name): Index
    {
        if (isset($this->aliases[$name])) {
            $name = $this->aliases[$name];
        }

        return $this->elasticSearch->getIndex($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection(DocumentMetadata $class): CollectionInterface
    {
        if (isset($this->collectionList[$class->name])) {
            return $this->collectionList[$class->name];
        }

        $collection = new Collection($this->documentManager, $class->name, $this->getSearchable($class));
        $collection->setResultCache($this->resultCache);

        return $collection;
    }

    public function setResultCache(?CacheItemPoolInterface $resultCache): void
    {
        $this->resultCache = $resultCache;
    }

    private function getSearchable(DocumentMetadata $class): SearchableInterface
    {
        list($indexName, $typeName) = explode('/', $class->typeName, 2) + [null, null];

        $searchable = $this->getIndex($indexName);
        if (null !== $typeName) {
            $searchable = $searchable->getType($typeName);
        }

        return $searchable;
    }
}

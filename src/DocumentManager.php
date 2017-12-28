<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica;

use Doctrine\Common\EventManager;
use Elastica\Client;
use Elastica\SearchableInterface;
use Fazland\ODM\Elastica\Metadata\DocumentMetadata;
use Fazland\ODM\Elastica\Metadata\MetadataFactory;
use Fazland\ODM\Elastica\Search\Executor;
use Fazland\ODM\Elastica\Type\TypeManager;
use Kcs\Metadata\Factory\MetadataFactoryInterface;
use ProxyManager\Factory\LazyLoadingGhostFactory;
use ProxyManager\Proxy\LazyLoadingInterface;
use ProxyManager\Proxy\ProxyInterface;

class DocumentManager implements DocumentManagerInterface
{
    /**
     * @var MetadataFactory
     */
    private $metadataFactory;

    /**
     * @var Client
     */
    private $elasticSearch;

    /**
     * @var LazyLoadingGhostFactory
     */
    private $proxyFactory;

    /**
     * @var TypeManager
     */
    private $typeManager;

    /**
     * @var UnitOfWork
     */
    private $unitOfWork;

    /**
     * @var Hydrator
     */
    private $hydrator;

    /**
     * @var Executor
     */
    private $queryExecutor;

    /**
     * @var EventManager
     */
    private $eventManager;

    public function __construct(Client $client, Configuration $configuration, EventManager $eventManager = null)
    {
        $this->elasticSearch = $client;
        $this->eventManager = $eventManager ?: new EventManager();

        $this->metadataFactory = $configuration->getMetadataFactory();
        $this->proxyFactory = $configuration->getProxyFactory();
        $this->typeManager = $configuration->getTypeManager();
        $this->hydrator = new Hydrator($this);
        $this->unitOfWork = new UnitOfWork($this, $this->hydrator);

        $this->clear();
        $this->queryExecutor = new Executor($this, $this->hydrator, $this->elasticSearch);

        if (null !== $resultCache = $configuration->getResultCacheImpl()) {
            $this->queryExecutor->setResultCacheImpl($resultCache);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function find($className, $id)
    {
        return $this->getUnitOfWork()->getDocumentPersister($className)->load(['_id' => $id]);
    }

    /**
     * {@inheritdoc}
     */
    public function persist($object): void
    {
        // TODO: Implement persist() method.
    }

    /**
     * {@inheritdoc}
     */
    public function remove($object): void
    {
        // TODO: Implement remove() method.
    }

    /**
     * {@inheritdoc}
     */
    public function merge($object)
    {
        // TODO: Implement merge() method.
    }

    /**
     * {@inheritdoc}
     */
    public function clear($objectName = null): void
    {
        $this->unitOfWork->clear($objectName);
    }

    /**
     * {@inheritdoc}
     */
    public function detach($object): void
    {
        $this->unitOfWork->detach($object);
    }

    /**
     * {@inheritdoc}
     */
    public function refresh($object): void
    {
        $class = $this->getClassMetadata(get_class($object));
        $persister = $this->unitOfWork->getDocumentPersister($class->name);

        $persister->load(['_id' => $class->getSingleIdentifier($object)], $object);
    }

    /**
     * {@inheritdoc}
     */
    public function flush(): void
    {
        // TODO: Implement flush() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository($className)
    {
        // TODO: Implement getRepository() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getClassMetadata($className): DocumentMetadata
    {
        if (is_object($className) && $className instanceof ProxyInterface) {
            $className = get_parent_class($className);
        }

        return $this->metadataFactory->getMetadataFor($className);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataFactory(): MetadataFactoryInterface
    {
        return $this->metadataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function initializeObject($obj): void
    {
        if ($obj instanceof LazyLoadingInterface) {
            $obj->initializeProxy();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function contains($object): bool
    {
        if (! is_object($object)) {
            throw new \InvalidArgumentException('Expected object, '.gettype($object).' given.');
        }

        return $this->unitOfWork->isInIdentityMap($object);
    }

    /**
     * {@inheritdoc}
     */
    public function getProxyFactory(): LazyLoadingGhostFactory
    {
        return $this->proxyFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventManager(): EventManager
    {
        return $this->eventManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getUnitOfWork(): UnitOfWork
    {
        return $this->unitOfWork;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeManager(): TypeManager
    {
        return $this->typeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection(string $className): SearchableInterface
    {
        $metadata = $this->getClassMetadata($className);
        list($indexName, $typeName) = explode('/', $metadata->typeName, 2);

        return $this->elasticSearch
            ->getIndex($indexName)
            ->getType($typeName);
    }
}

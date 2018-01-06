<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica;

use Doctrine\Common\EventManager;
use Elastica\Query;
use Fazland\ODM\Elastica\Collection\CollectionInterface;
use Fazland\ODM\Elastica\Collection\DatabaseInterface;
use Fazland\ODM\Elastica\Hydrator\HydratorInterface;
use Fazland\ODM\Elastica\Metadata\DocumentMetadata;
use Fazland\ODM\Elastica\Metadata\MetadataFactory;
use Fazland\ODM\Elastica\Persister\Hints;
use Fazland\ODM\Elastica\Repository\DocumentRepositoryInterface;
use Fazland\ODM\Elastica\Repository\RepositoryFactoryInterface;
use Fazland\ODM\Elastica\Search\Search;
use Fazland\ODM\Elastica\Type\TypeManager;
use Kcs\Metadata\Factory\MetadataFactoryInterface;
use ProxyManager\Factory\LazyLoadingGhostFactory;
use ProxyManager\Proxy\LazyLoadingInterface;
use ProxyManager\Proxy\ProxyInterface;
use Psr\Cache\CacheItemPoolInterface;

class DocumentManager implements DocumentManagerInterface
{
    /**
     * @var DatabaseInterface
     */
    private $database;

    /**
     * @var MetadataFactory
     */
    private $metadataFactory;

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
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var RepositoryFactoryInterface
     */
    private $repositoryFactory;

    /**
     * @var CacheItemPoolInterface|null
     */
    private $resultCache;

    public function __construct(DatabaseInterface $database, Configuration $configuration, EventManager $eventManager = null)
    {
        $this->database = $database;
        $this->eventManager = $eventManager ?: new EventManager();

        $this->metadataFactory = $configuration->getMetadataFactory();
        $this->proxyFactory = $configuration->getProxyFactory();
        $this->typeManager = $configuration->getTypeManager();
        $this->unitOfWork = new UnitOfWork($this);
        $this->repositoryFactory = $configuration->getRepositoryFactory();
        $this->resultCache = $configuration->getResultCache();

        $this->clear();
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
        if (! is_object($object)) {
            throw new \InvalidArgumentException('Expected object, '.gettype($object).' given.');
        }

        $this->unitOfWork->persist($object);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($object): void
    {
        if (! is_object($object)) {
            throw new \InvalidArgumentException('Expected object, '.gettype($object).' given.');
        }

        $this->unitOfWork->remove($object);
    }

    /**
     * {@inheritdoc}
     */
    public function merge($object)
    {
        if (! is_object($object)) {
            throw new \InvalidArgumentException('Expected object, '.gettype($object).' given.');
        }

        return $this->unitOfWork->merge($object);
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
        if (! is_object($object)) {
            throw new \InvalidArgumentException('Expected object, '.gettype($object).' given.');
        }

        $this->unitOfWork->detach($object);
    }

    /**
     * {@inheritdoc}
     */
    public function refresh($object): void
    {
        $class = $this->getClassMetadata(get_class($object));
        $persister = $this->unitOfWork->getDocumentPersister($class->name);

        $persister->load(['_id' => $class->getSingleIdentifier($object)], [Hints::HINT_REFRESH => true], $object);
    }

    /**
     * {@inheritdoc}
     */
    public function flush(): void
    {
        $this->unitOfWork->commit();
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository($className): DocumentRepositoryInterface
    {
        return $this->repositoryFactory->getRepository($this, $className);
    }

    /**
     * {@inheritdoc}
     */
    public function getClassMetadata($className): DocumentMetadata
    {
        if (is_subclass_of($className, ProxyInterface::class)) {
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
    public function getDatabase(): DatabaseInterface
    {
        return $this->database;
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
    public function getCollection(string $className): CollectionInterface
    {
        $class = $this->getClassMetadata($className);

        return $this->database->getCollection($class);
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
    public function newHydrator(int $hydrationMode): HydratorInterface
    {
        switch ($hydrationMode) {
            case HydratorInterface::HYDRATE_OBJECT:
                return new Hydrator\ObjectHydrator($this);
        }

        throw new \InvalidArgumentException('Invalid hydration mode '.$hydrationMode);
    }

    /**
     * {@inheritdoc}
     */
    public function createSearch(string $className): Search
    {
        $collection = $this->getCollection($className);

        return $collection->createSearch($this, Query::create(''));
    }
}

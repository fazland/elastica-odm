<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica;

use Doctrine\Common\EventManager;
use Elastica\Client;
use Fazland\ODM\Elastica\Collection\CollectionInterface;
use Fazland\ODM\Elastica\Collection\Database;
use Fazland\ODM\Elastica\Collection\DatabaseInterface;
use Fazland\ODM\Elastica\Hydrator\HydratorInterface;
use Fazland\ODM\Elastica\Metadata\DocumentMetadata;
use Fazland\ODM\Elastica\Metadata\MetadataFactory;
use Fazland\ODM\Elastica\Repository\DocumentRepositoryInterface;
use Fazland\ODM\Elastica\Repository\RepositoryFactoryInterface;
use Fazland\ODM\Elastica\Type\TypeManager;
use Kcs\Metadata\Factory\MetadataFactoryInterface;
use ProxyManager\Factory\LazyLoadingGhostFactory;
use ProxyManager\Proxy\LazyLoadingInterface;
use ProxyManager\Proxy\ProxyInterface;

class DocumentManager implements DocumentManagerInterface
{
    /**
     * @var Configuration
     */
    private $configuration;

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
     * @var DatabaseInterface
     */
    private $database;

    public function __construct(Client $elasticSearch, Configuration $configuration, EventManager $eventManager = null)
    {
        $this->configuration = $configuration;
        $this->database = new Database($elasticSearch, $this);
        $this->eventManager = $eventManager ?: new EventManager();

        $this->metadataFactory = $configuration->getMetadataFactory();
        $this->proxyFactory = $configuration->getProxyFactory();
        $this->typeManager = $configuration->getTypeManager();
        $this->unitOfWork = new UnitOfWork($this);

        $this->clear();

        if (null !== $resultCache = $configuration->getResultCache()) {
            $this->database->setResultCache($resultCache);
        }

        $this->repositoryFactory = $configuration->getRepositoryFactory();
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
    public function getRepository($className): DocumentRepositoryInterface
    {
        return $this->repositoryFactory->getRepository($className);
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
     * @return Configuration
     */
    public function getConfiguration(): Configuration
    {
        return $this->configuration;
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
    public function getCollection(string $className): CollectionInterface
    {
        $class = $this->getClassMetadata($className);

        return $this->database->getCollection($class);
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
}

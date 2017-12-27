<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica;

use Fazland\ODM\Elastica\Metadata\MetadataFactory;
use Fazland\ODM\Elastica\Search\Executor;
use Fazland\ODM\Elastica\Type\TypeManager;
use Elastica\Client;
use Elastica\Document;
use Elastica\Exception\NotFoundException;
use Elastica\Type;
use ProxyManager\Factory\LazyLoadingGhostFactory;
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

    public function __construct(Configuration $configuration)
    {
        $this->metadataFactory = $configuration->getMetadataFactory();
        $this->elasticSearch = $configuration->getClient();
        $this->proxyFactory = $configuration->getProxyFactory();
        $this->typeManager = $configuration->getTypeManager();

        $this->clear();

        $this->hydrator = new Hydrator($this);
        $this->queryExecutor = new Executor($this, $this->hydrator, $this->elasticSearch);

        if (null !== $resultCache = $configuration->getResultCacheImpl()) {
            $this->queryExecutor->setResultCacheImpl($resultCache);
        }
    }

    /**
     * @inheritDoc
     */
    public function find($className, $id)
    {
        $obj = $this->unitOfWork->tryGetById($className, $id);
        if (null !== $obj) {
            return $obj;
        }

        $document = $this->fetch($className, $id);
        $result = $this->hydrator->hydrateOne($document, $className);

        return $result;
    }

    /**
     * Fetches a document from the ES index.
     * Throws a NotFoundException if the document is not present.
     *
     * @param string $className
     * @param $id
     *
     * @return Document
     *
     * @throws NotFoundException
     */
    public function fetch(string $className, $id): Document
    {
        return $this->getElasticaType($className)->getDocument($id);
    }

    /**
     * @inheritDoc
     */
    public function persist($object)
    {
        // TODO: Implement persist() method.
    }

    /**
     * @inheritDoc
     */
    public function remove($object)
    {
        // TODO: Implement remove() method.
    }

    /**
     * @inheritDoc
     */
    public function merge($object)
    {
        // TODO: Implement merge() method.
    }

    /**
     * @inheritDoc
     */
    public function clear($objectName = null)
    {
        if ($objectName) {
            throw new \Exception('Not implemented yet');
        }

        $this->unitOfWork = new UnitOfWork($this);
    }

    /**
     * @inheritDoc
     */
    public function detach($object)
    {
        // TODO: Implement detach() method.
    }

    /**
     * @inheritDoc
     */
    public function refresh($object)
    {
        // TODO: Implement refresh() method.
    }

    /**
     * @inheritDoc
     */
    public function flush()
    {
        // TODO: Implement flush() method.
    }

    /**
     * @inheritDoc
     */
    public function getRepository($className)
    {
        // TODO: Implement getRepository() method.
    }

    /**
     * @inheritDoc
     */
    public function getClassMetadata($className)
    {
        if (is_object($className) && $className instanceof ProxyInterface) {
            $className = get_parent_class($className);
        }

        return $this->metadataFactory->getMetadataFor($className);
    }

    /**
     * @inheritDoc
     */
    public function getMetadataFactory()
    {
        return $this->metadataFactory;
    }

    /**
     * @inheritDoc
     */
    public function initializeObject($obj)
    {
        // TODO: Implement initializeObject() method.
    }

    /**
     * @inheritDoc
     */
    public function contains($object)
    {
        // TODO: Implement contains() method.
    }

    public function getProxyFactory(): LazyLoadingGhostFactory
    {
        return $this->proxyFactory;
    }

    public function getUnitOfWork(): UnitOfWork
    {
        return $this->unitOfWork;
    }

    public function getTypeManager(): TypeManager
    {
        return $this->typeManager;
    }

    private function getElasticaType($className): Type
    {
        $metadata = $this->getClassMetadata($className);
        list($indexName, $typeName) = explode('/', $metadata->typeName, 2);

        return $this->elasticSearch
            ->getIndex($indexName)
            ->getType($typeName);
    }
}

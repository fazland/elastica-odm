<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica;

use Fazland\ODM\Elastica\Exception\InvalidDocumentRepositoryException;
use Fazland\ODM\Elastica\Repository\DefaultRepositoryFactory;
use Fazland\ODM\Elastica\Repository\DocumentRepository;
use Fazland\ODM\Elastica\Repository\DocumentRepositoryInterface;
use Fazland\ODM\Elastica\Repository\RepositoryFactoryInterface;
use Fazland\ODM\Elastica\Type\TypeManager;
use Kcs\Metadata\Factory\MetadataFactoryInterface;
use ProxyManager\Factory\LazyLoadingGhostFactory;
use Psr\Cache\CacheItemPoolInterface;

final class Configuration
{
    /**
     * The document metadata factory.
     *
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * The document proxy factory.
     *
     * @var LazyLoadingGhostFactory
     */
    private $proxyFactory;

    /**
     * The result cache implementation.
     *
     * @var CacheItemPoolInterface
     */
    private $resultCache;

    /**
     * The type manager.
     *
     * @var TypeManager
     */
    private $typeManager;

    /**
     * @var RepositoryFactoryInterface|null
     */
    private $repositoryFactory;

    /**
     * @var string|null
     */
    private $defaultRepositoryClassName;

    public function __construct()
    {
        $this->typeManager = new TypeManager();
    }

    /**
     * Sets the document proxy factory.
     *
     * @param LazyLoadingGhostFactory $proxyFactory
     * @required
     *
     * @return $this
     */
    public function setProxyFactory(LazyLoadingGhostFactory $proxyFactory): self
    {
        $this->proxyFactory = $proxyFactory;

        return $this;
    }

    /**
     * Sets the metadata factory.
     *
     * @param MetadataFactoryInterface $metadataFactory
     * @required
     *
     * @return $this
     */
    public function setMetadataFactory(MetadataFactoryInterface $metadataFactory): self
    {
        $this->metadataFactory = $metadataFactory;

        return $this;
    }

    /**
     * Sets the result cache implementation.
     *
     * @param CacheItemPoolInterface $resultCache
     *
     * @return $this
     */
    public function setResultCache(?CacheItemPoolInterface $resultCache = null): self
    {
        $this->resultCache = $resultCache;

        return $this;
    }

    /**
     * Sets the type manager.
     *
     * @param TypeManager $typeManager
     *
     * @return $this
     */
    public function setTypeManager(TypeManager $typeManager): self
    {
        $this->typeManager = $typeManager;

        return $this;
    }

    /**
     * Sets the repository factory.
     *
     * @param RepositoryFactoryInterface|null $repositoryFactory
     *
     * @return $this
     */
    public function setRepositoryFactory(?RepositoryFactoryInterface $repositoryFactory): self
    {
        $this->repositoryFactory = $repositoryFactory;

        return $this;
    }

    /**
     * Sets default repository class.
     *
     * @param string $className
     *
     * @throws InvalidDocumentRepositoryException
     */
    public function setDefaultRepositoryClassName($className): void
    {
        $reflectionClass = new \ReflectionClass($className);

        if (! $reflectionClass->implementsInterface(DocumentRepositoryInterface::class)) {
            throw new InvalidDocumentRepositoryException($className);
        }

        $this->defaultRepositoryClassName = $className;
    }

    /**
     * Gets the document proxy factory.
     *
     * @return LazyLoadingGhostFactory
     */
    public function getProxyFactory(): LazyLoadingGhostFactory
    {
        return $this->proxyFactory;
    }

    /**
     * Sets the metadata factory.
     *
     * @return MetadataFactoryInterface
     */
    public function getMetadataFactory(): MetadataFactoryInterface
    {
        return $this->metadataFactory;
    }

    /**
     * Gets the result cache implementation.
     *
     * @return CacheItemPoolInterface|null
     */
    public function getResultCache(): ?CacheItemPoolInterface
    {
        return $this->resultCache;
    }

    /**
     * Gets the type manager.
     *
     * @return TypeManager
     */
    public function getTypeManager(): TypeManager
    {
        return $this->typeManager;
    }

    /**
     * Sets the repository factory.
     *
     * @return RepositoryFactoryInterface
     */
    public function getRepositoryFactory(): RepositoryFactoryInterface
    {
        if (null !== $this->repositoryFactory) {
            return $this->repositoryFactory;
        }

        $factory = new DefaultRepositoryFactory();
        $factory->setDefaultRepositoryClassName($this->getDefaultRepositoryClassName());

        return $factory;
    }

    /**
     * Get default repository class.
     *
     * @return string
     */
    public function getDefaultRepositoryClassName(): string
    {
        return $this->defaultRepositoryClassName ?: DocumentRepository::class;
    }
}

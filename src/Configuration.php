<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica;

use Fazland\ODM\Elastica\Type\TypeManager;
use Elastica\Client;
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
    private $resultCacheImpl;

    /**
     * The type manager.
     *
     * @var TypeManager
     */
    private $typeManager;

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
     * @return $this|self
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
     * @return $this|self
     */
    public function setMetadataFactory(MetadataFactoryInterface $metadataFactory): self
    {
        $this->metadataFactory = $metadataFactory;

        return $this;
    }

    /**
     * Sets the result cache implementation.
     *
     * @param CacheItemPoolInterface $resultCacheImpl
     *
     * @return $this|self
     */
    public function setResultCacheImpl(?CacheItemPoolInterface $resultCacheImpl = null): self
    {
        $this->resultCacheImpl = $resultCacheImpl;

        return $this;
    }

    /**
     * Sets the type manager implementation.
     *
     * @param TypeManager $typeManager
     *
     * @return $this|self
     */
    public function setTypeManager(TypeManager $typeManager): self
    {
        $this->typeManager = $typeManager;

        return $this;
    }

    public function getProxyFactory(): LazyLoadingGhostFactory
    {
        return $this->proxyFactory;
    }

    public function getMetadataFactory(): MetadataFactoryInterface
    {
        return $this->metadataFactory;
    }

    public function getResultCacheImpl(): ?CacheItemPoolInterface
    {
        return $this->resultCacheImpl;
    }

    public function getTypeManager(): TypeManager
    {
        return $this->typeManager;
    }
}

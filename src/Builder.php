<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica;

use Elastica\Client;
use Fazland\ODM\Elastica\Collection\Database;
use Fazland\ODM\Elastica\Metadata\Loader;
use Fazland\ODM\Elastica\Metadata\MetadataFactory;
use Fazland\ODM\Elastica\Type\TypeInterface;
use Fazland\ODM\Elastica\Type\TypeManager;
use ProxyManager\Factory\LazyLoadingGhostFactory;
use Psr\Log\LoggerInterface;

final class Builder
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $connectionUrl;

    /**
     * @var int
     */
    private $timeout;

    /**
     * @var int
     */
    private $connectTimeout;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var LazyLoadingGhostFactory
     */
    private $proxyFactory;

    /**
     * @var MetadataFactory
     */
    private $metadataFactory;

    /**
     * @var TypeManager
     */
    private $typeManager;

    /**
     * @var bool
     */
    private $addDefaultTypes = true;

    /**
     * @var Loader\LoaderInterface
     */
    private $metadataLoader = null;

    public static function create(): self
    {
        return new self();
    }

    public function __construct()
    {
        $this->connectionUrl = 'http://localhost:9200/';
        $this->timeout = 30;
        $this->connectTimeout = 5;
        $this->typeManager = new TypeManager();
    }

    public function setClient(Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function setConnectionUrl(string $connectionUrl): self
    {
        $this->connectionUrl = $connectionUrl;

        return $this;
    }

    public function setTimeout(int $timeout, int $connectTimeout = 5): self
    {
        $this->timeout = $timeout;
        $this->connectTimeout = $connectTimeout;

        return $this;
    }

    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function setProxyFactory(LazyLoadingGhostFactory $proxyFactory): self
    {
        $this->proxyFactory = $proxyFactory;

        return $this;
    }

    public function setMetadataFactory(MetadataFactory $metadataFactory): self
    {
        $this->metadataFactory = $metadataFactory;

        return $this;
    }

    public function addType(TypeInterface $type): self
    {
        $this->addDefaultTypes = false;
        $this->typeManager->addType($type);

        return $this;
    }

    public function addDefaultTypes(): self
    {
        return $this
            ->addType(new Type\BinaryType())
            ->addType(new Type\BooleanType())
            ->addType(new Type\DateTimeImmutableType())
            ->addType(new Type\DateTimeType())
            ->addType(new Type\FloatType())
            ->addType(new Type\GeoPointType())
            ->addType(new Type\GeoShapeType())
            ->addType(new Type\IntegerType())
            ->addType(new Type\IpType())
            ->addType(new Type\PercolatorType())
            ->addType(new Type\StringType())
            ->addType(new Type\RawType())
        ;
    }

    public function addMetadataLoader(Loader\LoaderInterface $loader): self
    {
        if (null === $this->metadataLoader) {
            $this->metadataLoader = $loader;
        } elseif ($this->metadataLoader instanceof Loader\ChainLoader) {
            $this->metadataLoader->addLoader($loader);
        } else {
            $this->metadataLoader = new Loader\ChainLoader([$this->metadataLoader, $loader]);
        }

        return $this;
    }

    public function build(): DocumentManager
    {
        if (null === $this->client) {
            $this->client = new Client([
                'url' => $this->connectionUrl,
                'connectTimeout' => $this->connectTimeout,
                'timeout' => $this->timeout,
            ], null, $this->logger);
        }

        if (null === $this->proxyFactory) {
            $this->proxyFactory = new LazyLoadingGhostFactory();
        }

        if (null === $this->metadataFactory) {
            if (null === $this->metadataLoader) {
                throw new \InvalidArgumentException('You must define at least one metadata loader');
            }

            $this->metadataFactory = new MetadataFactory($this->metadataLoader);
        }

        if ($this->addDefaultTypes) {
            $this->addDefaultTypes();
        }

        $configuration = new Configuration();
        $configuration->setMetadataFactory($this->metadataFactory);
        $configuration->setProxyFactory($this->proxyFactory);
        $configuration->setTypeManager($this->typeManager);

        return new DocumentManager(new Database($this->client), $configuration);
    }
}

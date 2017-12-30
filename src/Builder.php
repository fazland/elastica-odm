<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica;

use Elastica\Client;
use Fazland\ODM\Elastica\Collection\Database;
use Fazland\ODM\Elastica\Metadata\Loader;
use Fazland\ODM\Elastica\Metadata\MetadataFactory;
use Fazland\ODM\Elastica\Metadata\Processor;
use Fazland\ODM\Elastica\Type\DateTimeType;
use Fazland\ODM\Elastica\Type\RawType;
use Fazland\ODM\Elastica\Type\TypeInterface;
use Fazland\ODM\Elastica\Type\TypeManager;
use Kcs\Metadata\Loader\Processor\ProcessorFactory;
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
            ->addType(new RawType())
            ->addType(new DateTimeType())
        ;
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
            $processorFactory = new ProcessorFactory();
            $processorFactory->registerProcessor(Annotation\Document::class, Processor\DocumentProcessor::class);
            $processorFactory->registerProcessor(Annotation\DocumentId::class, Processor\DocumentIdProcessor::class);
            $processorFactory->registerProcessor(Annotation\IndexName::class, Processor\IndexNameProcessor::class);
            $processorFactory->registerProcessor(Annotation\TypeName::class, Processor\TypeNameProcessor::class);
            $processorFactory->registerProcessor(Annotation\Field::class, Processor\FieldProcessor::class);

            $this->metadataFactory = new MetadataFactory(new Loader($processorFactory));
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

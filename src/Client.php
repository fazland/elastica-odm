<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica;

use Elastica\Client as BaseClient;
use Elastica\Index;
use Psr\Log\LoggerInterface;

class Client extends BaseClient
{
    /**
     * @var string[]
     */
    private $aliases;

    public function __construct(array $config = [], ?callable $callback = null, LoggerInterface $logger = null)
    {
        parent::__construct($config, $callback, $logger);

        $this->aliases = [];
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

        return parent::getIndex($name);
    }
}

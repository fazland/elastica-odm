<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Exception;

class CannotDropAnAliasException extends \RuntimeException
{
    public function __construct(string $indexName, \Throwable $previous = null)
    {
        parent::__construct(\sprintf('"%s" is an alias and cannot be dropped.', $indexName), 0, $previous);
    }
}

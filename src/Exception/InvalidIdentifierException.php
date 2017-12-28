<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Exception;

use Fazland\ODM\Elastica\Repository\DocumentRepositoryInterface;

class InvalidIdentifierException extends \Exception implements ExceptionInterface
{
    public function __construct(string $className)
    {
        parent::__construct(sprintf(
            "Invalid repository class '%s'. It must be a %s.",
            $className,
            DocumentRepositoryInterface::class
        ));
    }
}

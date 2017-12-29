<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Exception;

use Fazland\ODM\Elastica\Repository\DocumentRepositoryInterface;

class InvalidDocumentRepositoryException extends \Exception implements ExceptionInterface
{
    public function __construct(string $className)
    {
        parent::__construct("Repository class '".$className."' is invalid. It must implement ".DocumentRepositoryInterface::class.".");
    }
}

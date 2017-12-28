<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Repository;

use Fazland\ODM\Elastica\DocumentManagerInterface;
use Fazland\ODM\Elastica\Metadata\DocumentMetadata;

final class DefaultRepositoryFactory extends AbstractRepositoryFactory
{
    protected function instantiateRepository(
        string $repositoryClassName,
        DocumentManagerInterface $documentManager,
        DocumentMetadata $class
    ): DocumentRepositoryInterface {
        return new $repositoryClassName($documentManager, $class);
    }
}

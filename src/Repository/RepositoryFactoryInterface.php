<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Repository;

use Fazland\ODM\Elastica\DocumentManagerInterface;

interface RepositoryFactoryInterface
{
    /**
     * Gets the repository for a document class.
     *
     * @param DocumentManagerInterface $documentManager
     * @param string $documentName
     *
     * @return DocumentRepositoryInterface
     */
    public function getRepository(DocumentManagerInterface $documentManager, string $documentName): DocumentRepositoryInterface;

    /**
     * Sets the default repository fully-qualified class name.
     *
     * @param string $defaultRepositoryClassName
     *
     * @return void
     */
    public function setDefaultRepositoryClassName(string $defaultRepositoryClassName): void;
}

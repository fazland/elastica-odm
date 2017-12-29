<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Repository;

use Fazland\ODM\Elastica\DocumentManagerInterface;
use Fazland\ODM\Elastica\Metadata\DocumentMetadata;

/**
 * Abstract factory for creating document repositories.
 */
abstract class AbstractRepositoryFactory implements RepositoryFactoryInterface
{
    /**
     * @var string
     */
    private $defaultRepositoryClassName;

    /**
     * The list of DocumentRepository instances.
     *
     * @var DocumentRepositoryInterface[]
     */
    private $repositoryList = [];

    /**
     * {@inheritdoc}
     */
    public function getRepository(DocumentManagerInterface $documentManager, string $documentName): DocumentRepositoryInterface
    {
        $metadata = $documentManager->getClassMetadata($documentName);
        $hashKey = $metadata->getName().spl_object_hash($documentManager);

        if (isset($this->repositoryList[$hashKey])) {
            return $this->repositoryList[$hashKey];
        }

        $repository = $this->createRepository($documentManager, ltrim($documentName, '\\'));
        $this->repositoryList[$hashKey] = $repository;

        return $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultRepositoryClassName(string $defaultRepositoryClassName): void
    {
        $this->defaultRepositoryClassName = $defaultRepositoryClassName;
    }

    /**
     * Create a new repository instance for a document class.
     *
     * @param DocumentManagerInterface $documentManager
     * @param string                   $documentName
     *
     * @return DocumentRepositoryInterface
     */
    protected function createRepository(DocumentManagerInterface $documentManager, string $documentName): DocumentRepositoryInterface
    {
        $class = $documentManager->getClassMetadata($documentName);
        $repositoryClassName = $class->customRepositoryClassName ?: $this->defaultRepositoryClassName;

        return $this->instantiateRepository($repositoryClassName, $documentManager, $class);
    }

    /**
     * Instantiates requested repository.
     *
     * @param string                   $repositoryClassName
     * @param DocumentManagerInterface $documentManager
     * @param DocumentMetadata         $metadata
     *
     * @return DocumentRepositoryInterface
     */
    abstract protected function instantiateRepository(
        string $repositoryClassName,
        DocumentManagerInterface $documentManager,
        DocumentMetadata $metadata
    ): DocumentRepositoryInterface;
}

<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Repository;

use Doctrine\Common\Collections\Criteria;
use Fazland\ODM\Elastica\DocumentManagerInterface;
use Fazland\ODM\Elastica\Metadata\DocumentMetadata;
use Fazland\ODM\Elastica\Persister\DocumentPersister;
use Fazland\ODM\Elastica\UnitOfWork;

class DocumentRepository implements DocumentRepositoryInterface
{
    /**
     * @var DocumentManagerInterface
     */
    protected $documentManager;

    /**
     * @var DocumentMetadata
     */
    protected $class;

    /**
     * @var string
     */
    protected $documentName;

    /**
     * @var UnitOfWork
     */
    private $unitOfWork;

    /**
     * @var DocumentPersister
     */
    private $persister;

    public function __construct(DocumentManagerInterface $documentManager, DocumentMetadata $class)
    {
        $this->documentManager = $documentManager;
        $this->class = $class;
        $this->documentName = $class->name;
        $this->unitOfWork = $documentManager->getUnitOfWork();
        $this->persister = $this->unitOfWork->getDocumentPersister($this->documentName);
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        return $this->documentManager->find($this->documentName, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return $this->findBy([]);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): array
    {
        return $this->persister->loadAll($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria)
    {
        return $this->persister->load($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function getDocumentName(): string
    {
        return $this->documentName;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName(): string
    {
        return $this->getDocumentName();
    }

    /**
     * {@inheritdoc}
     */
    public function matching(Criteria $criteria)
    {
        // TODO: Implement matching() method.
    }
}

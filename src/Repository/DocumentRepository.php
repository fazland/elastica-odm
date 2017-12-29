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
    protected $dm;

    /**
     * @var DocumentMetadata
     */
    protected $class;

    /**
     * @var string
     */
    protected $documentClass;

    /**
     * @var UnitOfWork
     */
    protected $uow;

    public function __construct(DocumentManagerInterface $documentManager, DocumentMetadata $class)
    {
        $this->dm = $documentManager;
        $this->class = $class;
        $this->documentClass = $class->name;
        $this->uow = $documentManager->getUnitOfWork();
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        return $this->dm->find($this->documentClass, $id);
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
        return $this->getDocumentPersister()->loadAll($criteria, $orderBy, $limit, $offset);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria)
    {
        return $this->getDocumentPersister()->load($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName(): string
    {
        return $this->documentClass;
    }

    /**
     * {@inheritdoc}
     */
    public function matching(Criteria $criteria)
    {
        // TODO: Implement matching() method.
    }

    /**
     * Gets the document persister for this document class.
     *
     * @return DocumentPersister
     */
    protected function getDocumentPersister(): DocumentPersister
    {
        return $this->uow->getDocumentPersister($this->documentClass);
    }
}

<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Events;

use Doctrine\Common\EventManager;
use Fazland\ODM\Elastica\Events;
use Fazland\ODM\Elastica\Metadata\DocumentMetadata;
use Fazland\ODM\Elastica\UnitOfWork;

class LifecycleEventManager
{
    /**
     * @var UnitOfWork
     */
    private $uow;

    /**
     * @var EventManager
     */
    private $evm;

    public function __construct(UnitOfWork $uow, EventManager $evm)
    {
        $this->uow = $uow;
        $this->evm = $evm;
    }

    public function postPersist(DocumentMetadata $class, $object)
    {
        // @todo
    }

    public function prePersist(DocumentMetadata $class, $object)
    {
        // @todo
    }

    public function preRemove(DocumentMetadata $class, $object)
    {
        // @todo
    }

    public function postRemove(DocumentMetadata $class, $object)
    {
        // @todo
    }

    public function preUpdate(DocumentMetadata $class, $document)
    {
        // @todo Check lifecycle callbacks

        if ($this->evm->hasListeners(Events::preUpdate)) {
            $this->evm->dispatchEvent(Events::preUpdate, $this->uow->getDocumentChangeSet($document));
            $this->uow->recomputeSingleDocumentChangeset($class, $document);
        }
    }

    public function postUpdate(DocumentMetadata $class, $document)
    {
        // @todo
    }
}

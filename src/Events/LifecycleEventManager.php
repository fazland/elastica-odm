<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Events;

use Doctrine\Common\EventManager;
use Fazland\ODM\Elastica\Metadata\DocumentMetadata;

class LifecycleEventManager
{
    /**
     * @var EventManager
     */
    private $evm;

    public function __construct(EventManager $evm)
    {
        $this->evm = $evm;
    }

    public function prePersist(DocumentMetadata $class, $object)
    {
        // @todo
    }

    public function postPersist(DocumentMetadata $class, $object)
    {
        // @todo
    }
}
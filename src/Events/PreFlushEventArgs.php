<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Events;

use Doctrine\Common\EventArgs;
use Fazland\ODM\Elastica\DocumentManagerInterface;

class PreFlushEventArgs extends EventArgs
{
    /**
     * @var DocumentManagerInterface
     */
    private $dm;

    public function __construct(DocumentManagerInterface $dm)
    {
        $this->dm = $dm;
    }

    public function getDocumentManager()
    {
        return $this->dm;
    }
}

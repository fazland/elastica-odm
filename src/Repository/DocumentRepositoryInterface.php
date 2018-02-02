<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Repository;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectRepository;
use Fazland\ODM\Elastica\Search\Search;

interface DocumentRepositoryInterface extends ObjectRepository, Selectable
{
    /**
     * Creates a Search object for the current class.
     *
     * @return Search
     */
    public function createSearch(): Search;
}

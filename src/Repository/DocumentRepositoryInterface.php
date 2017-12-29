<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Repository;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectRepository;

interface DocumentRepositoryInterface extends ObjectRepository, Selectable
{
}

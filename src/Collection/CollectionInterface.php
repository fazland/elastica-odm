<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Collection;

use Elastica\Query;
use Elastica\ResultSet;
use Elastica\Scroll;
use Fazland\ODM\Elastica\Search\Search;
use Psr\Cache\CacheItemPoolInterface;

interface CollectionInterface
{
    public function search(Query $query): ResultSet;

    public function scroll(Query $query): Scroll;

    public function getResultCache(): ?CacheItemPoolInterface;

    public function setResultCache(?CacheItemPoolInterface $resultCache): void;

    public function createSearch(): Search;

    public function count(Query $query): int;
}

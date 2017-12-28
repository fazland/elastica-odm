<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Collection;

use Fazland\ODM\Elastica\Metadata\DocumentMetadata;

interface DatabaseInterface
{
    public function getCollection(DocumentMetadata $class): CollectionInterface;
}

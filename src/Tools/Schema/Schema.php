<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tools\Schema;

class Schema
{
    private $collectionMapping = [];

    public function addCollection(Collection $collection)
    {
        $metadata = $collection->getDocumentMetadata();
        $this->collectionMapping[$metadata->collectionName] = $collection;
    }

    public function getMapping(): array
    {
        return $this->collectionMapping;
    }
}

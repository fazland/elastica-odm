<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tools\Schema;

/**
 * Holds the informations about the schema of collections.
 */
class Schema
{
    private $collectionMapping = [];

    /**
     * Adds a collection to the schema.
     *
     * @param Collection $collection
     */
    public function addCollection(Collection $collection)
    {
        $metadata = $collection->getDocumentMetadata();
        $this->collectionMapping[$metadata->getName()] = $collection;
    }

    /**
     * Gets the collection mappings.
     *
     * @return Collection[]
     */
    public function getMapping(): array
    {
        return $this->collectionMapping;
    }
}

<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tools\Schema;

use Elastica\Type\Mapping;
use Fazland\ODM\Elastica\Metadata\DocumentMetadata;

class Collection
{
    /**
     * @var DocumentMetadata
     */
    private $documentMetadata;

    /**
     * @var Mapping
     */
    private $mapping;

    public function __construct(DocumentMetadata $documentMetadata, Mapping $mapping)
    {
        $this->documentMetadata = $documentMetadata;
        $this->mapping = $mapping;
    }

    /**
     * @return DocumentMetadata
     */
    public function getDocumentMetadata(): DocumentMetadata
    {
        return $this->documentMetadata;
    }

    /**
     * @return Mapping
     */
    public function getMapping(): Mapping
    {
        return $this->mapping;
    }
}

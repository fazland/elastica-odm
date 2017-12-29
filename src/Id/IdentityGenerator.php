<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Id;

use Fazland\ODM\Elastica\DocumentManagerInterface;

final class IdentityGenerator extends AbstractIdGenerator
{
    /**
     * {@inheritdoc}
     */
    public function generate(DocumentManagerInterface $dm, $document)
    {
        $collection = $dm->getCollection(get_class($document));

        return $collection->getLastInsertedId();
    }

    public function isPostInsertGenerator(): bool
    {
        return true;
    }
}

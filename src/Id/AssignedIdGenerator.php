<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Id;

use Fazland\ODM\Elastica\DocumentManagerInterface;
use Fazland\ODM\Elastica\Exception\InvalidIdentifierException;
use Fazland\ODM\Elastica\Metadata\DocumentMetadata;

final class AssignedIdGenerator extends AbstractIdGenerator
{
    /**
     * {@inheritdoc}
     */
    public function generate(DocumentManagerInterface $dm, $document)
    {
        /** @var DocumentMetadata $class */
        $class = $dm->getClassMetadata(\get_class($document));
        $id = $class->getSingleIdentifier($document);

        if (null === $id) {
            throw new InvalidIdentifierException(
                'Document of type "'.$class->name.'" is missing an assigned ID.'.
                'NONE generator strategy requires the ID field to be populated before persist is called.'
            );
        }

        return $id;
    }
}

<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Metadata;

use Kcs\Metadata\ClassMetadataInterface;
use Kcs\Metadata\Exception\InvalidMetadataException;
use Kcs\Metadata\Factory\AbstractMetadataFactory;

class MetadataFactory extends AbstractMetadataFactory
{
    /**
     * {@inheritdoc}
     */
    protected function validate(ClassMetadataInterface $classMetadata): void
    {
        if (! $classMetadata instanceof DocumentMetadata) {
            return;
        }

        $identifier = null;

        foreach ($classMetadata->getAttributesMetadata() as $attributesMetadata) {
            $count = 0;

            if (! $attributesMetadata instanceof FieldMetadata) {
                continue;
            }

            if ($attributesMetadata->identifier) {
                if (null !== $identifier) {
                    throw new InvalidMetadataException('@DocumentId should be declare at most once per class.');
                }

                $identifier = $attributesMetadata;
                ++$count;
            }

            if ($attributesMetadata->typeName) {
                ++$count;
            }

            if ($attributesMetadata->indexName) {
                ++$count;
            }

            if ($count > 1) {
                throw new InvalidMetadataException('@DocumentId, @IndexName and @TypeName are mutually exclusive. Please select one for "'.$attributesMetadata->getName().'"');
            }
        }

        $classMetadata->identifier = $identifier;
    }

    /**
     * {@inheritdoc}
     */
    protected function createMetadata(\ReflectionClass $class): ClassMetadataInterface
    {
        return new DocumentMetadata($class);
    }
}

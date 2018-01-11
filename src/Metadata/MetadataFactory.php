<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Metadata;

use Doctrine\Common\EventManager;
use Kcs\Metadata\ClassMetadataInterface;
use Kcs\Metadata\Exception\InvalidMetadataException;
use Kcs\Metadata\Factory\AbstractMetadataFactory;
use Kcs\Metadata\Loader\LoaderInterface;

class MetadataFactory extends AbstractMetadataFactory
{
    /**
     * @var EventManager
     */
    private $eventManager;

    public function __construct(LoaderInterface $loader, $cache = null)
    {
        parent::__construct($loader, null, $cache);
    }

    /**
     * Sets the event manager for this metadata factory.
     *
     * @param EventManager $eventManager
     */
    public function setEventManager(EventManager $eventManager): void
    {
        $this->eventManager = $eventManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function dispatchClassMetadataLoadedEvent(ClassMetadataInterface $classMetadata): void
    {
        // @todo
    }

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
                    throw new InvalidMetadataException('@DocumentId should be declared at most once per class.');
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
        $classMetadata->eagerFieldNames = array_filter($classMetadata->eagerFieldNames);
    }

    /**
     * {@inheritdoc}
     */
    protected function createMetadata(\ReflectionClass $class): ClassMetadataInterface
    {
        return new DocumentMetadata($class);
    }
}

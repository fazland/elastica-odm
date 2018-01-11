<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Metadata;

use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Fazland\ODM\Elastica\Metadata\Loader\LoaderInterface;
use Kcs\Metadata\ClassMetadataInterface;
use Kcs\Metadata\Exception\InvalidMetadataException;
use Kcs\Metadata\Factory\AbstractMetadataFactory;

class MetadataFactory extends AbstractMetadataFactory implements ClassMetadataFactory
{
    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var EventManager
     */
    private $eventManager;

    public function __construct(LoaderInterface $loader, $cache = null)
    {
        $this->loader = $loader;

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
     * Gets all the metadata available in this factory.
     *
     * @return DocumentMetadata[]
     */
    public function getAllMetadata(): array
    {
        $metadatas = [];
        foreach ($this->loader->getAllClassNames() as $className) {
            $metadatas[] = $this->getMetadataFor($className);
        }

        return $metadatas;
    }

    public function setMetadataFor($className, $class)
    {
        // @todo
    }

    public function isTransient($className)
    {
        // @todo
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

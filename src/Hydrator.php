<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica;

use Doctrine\Instantiator\Instantiator;
use Doctrine\Instantiator\InstantiatorInterface;
use Elastica\Document;
use Elastica\Exception\InvalidException;
use Elastica\ResultSet;
use Fazland\ODM\Elastica\Metadata\FieldMetadata;
use ProxyManager\Factory\LazyLoadingGhostFactory;
use ProxyManager\Proxy\GhostObjectInterface;

class Hydrator
{
    /**
     * @var DocumentManagerInterface
     */
    private $manager;

    /**
     * @var LazyLoadingGhostFactory
     */
    private $proxyFactory;

    public function __construct(DocumentManagerInterface $manager)
    {
        $this->manager = $manager;
        $this->proxyFactory = $this->manager->getProxyFactory();
    }

    public function hydrateAll(ResultSet $resultSet, string $className)
    {
        if (0 === $resultSet->count()) {
            return [];
        }

        $class = $this->manager->getClassMetadata($className);

        try {
            $source = $resultSet->getQuery()->getParam('_source');
        } catch (InvalidException $ex) {
            $source = null;
        }

        if (null !== $source) {
            $fields = false === $source ? [] : $source;
            $instantiator = new class($fields, $this) implements InstantiatorInterface {
                private $fields;
                private $hydrator;

                public function __construct(array $fields, Hydrator $hydrator)
                {
                    $this->fields = $fields;
                    $this->hydrator = $hydrator;
                }

                public function instantiate($className)
                {
                    return $this->hydrator->createProxy($className, $this->fields);
                }
            };
        } else {
            $instantiator = $this->getInstantiator();
        }

        $results = [];

        foreach ($resultSet as $result) {
            $document = $result->getDocument();
            $object = $this->manager->getUnitOfWork()->tryGetById($document->getId(), $class);

            if (null === $object) {
                $object = $instantiator->instantiate($className);
                $this->manager->getUnitOfWork()->createDocument($document, $object);
            }

            $results[] = $object;
        }

        return $results;
    }

    public function hydrateOne(Document $document, string $className)
    {
        $result = $this->getInstantiator()->instantiate($className);
        $this->manager->getUnitOfWork()->createDocument($document, $result);

        return $result;
    }

    public function getInstantiator(): Instantiator
    {
        static $instantiator = null;
        if (null === $instantiator) {
            $instantiator = new Instantiator();
        }

        return $instantiator;
    }

    public function createProxy(string $className, array $fields)
    {
        $metadata = $this->manager->getClassMetadata($className);
        $allowedMethods = array_map(function (string $field) {
            return 'get'.$field;
        }, $fields);

        $initializer = function (
            GhostObjectInterface $ghostObject,
            string $method,
            array $parameters,
            &$initializer,
            array $properties
        ) use ($fields, $allowedMethods, $metadata, $className) {
            if (('__set' === $method || '__get' === $method) && in_array($parameters['name'], $fields)) {
                return false;
            }

            if (in_array(strtolower($method), $allowedMethods)) {
                return false;
            }

            $initializer = null;

            $fields = [];
            foreach ($metadata->getAttributesMetadata() as $field) {
                if (! $field instanceof FieldMetadata) {
                    continue;
                }

                $fields[] = $field->name;
            }

            $id = $metadata->getIdentifierValues($ghostObject);
            $document = $this->manager->fetch($className, reset($id));
            $this->manager->getUnitOfWork()->createDocument($document, $ghostObject, $fields);

            return true;
        };

        $skippedProperties = [];

        foreach ($metadata->attributesMetadata as $metadata) {
            if (! $metadata instanceof FieldMetadata) {
                continue;
            }

            if (! $metadata->identifier && ! $metadata->typeName && ! $metadata->indexName) {
                continue;
            }

            $propRefl = $metadata->getReflection();

            if ($propRefl->isPrivate()) {
                $skippedProperties[] = "\0{$metadata->class}\0{$metadata->name}";
            } elseif ($propRefl->isProtected()) {
                $skippedProperties[] = "\0*\0{$metadata->name}";
            } else {
                $skippedProperties[] = $metadata->name;
            }
        }

        $proxyOptions = [
            'skippedProperties' => $skippedProperties,
        ];

        return $this->proxyFactory->createProxy($className, $initializer, $proxyOptions);
    }
}

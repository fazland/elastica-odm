<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Hydrator\Internal;

use Doctrine\Instantiator\InstantiatorInterface;
use Fazland\ODM\Elastica\DocumentManagerInterface;
use Fazland\ODM\Elastica\Metadata\DocumentMetadata;
use Fazland\ODM\Elastica\Metadata\FieldMetadata;
use ProxyManager\Proxy\GhostObjectInterface;

class ProxyInstantiator implements InstantiatorInterface
{
    /**
     * @var string[]
     */
    private $fields;

    /**
     * @var DocumentManagerInterface
     */
    private $manager;

    public function __construct(array $fields, DocumentManagerInterface $manager)
    {
        $this->fields = $fields;
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function instantiate($className)
    {
        return $this->createProxy($className, $this->fields);
    }

    private function createProxy(string $className, array $fields)
    {
        /** @var DocumentMetadata $class */
        $class = $this->manager->getClassMetadata($className);

        $allowedMethods = array_map(function (string $field) {
            return 'get'.$field;
        }, $fields);

        $initializer = function (
            GhostObjectInterface $ghostObject,
            string $method,
            array $parameters,
            &$initializer
        ) use ($fields, $allowedMethods): bool {
            if (('__get' === $method && '__set' === $method) && in_array($parameters['name'], $fields)) {
                return false;
            }

            if (in_array(strtolower($method), $allowedMethods)) {
                return false;
            }

            $initializer = null;
            $this->manager->refresh($ghostObject);

            return true;
        };

        $skippedProperties = [];
        foreach ($class->attributesMetadata as $field) {
            if (! $field instanceof FieldMetadata) {
                continue;
            }

            if (! $field->identifier && ! $field->typeName && ! $field->indexName) {
                continue;
            }

            $propRefl = $field->getReflection();

            if ($propRefl->isPrivate()) {
                $skippedProperties[] = "\0{$field->class}\0{$field->name}";
            } elseif ($propRefl->isProtected()) {
                $skippedProperties[] = "\0*\0{$field->name}";
            } else {
                $skippedProperties[] = $field->name;
            }
        }

        $proxyOptions = [
            'skippedProperties' => $skippedProperties,
        ];

        return $this->manager->getProxyFactory()->createProxy($className, $initializer, $proxyOptions);
    }
}

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
    public function instantiate($className): GhostObjectInterface
    {
        return $this->createProxy($className, $this->fields);
    }

    private function createProxy(string $className, array $fields): GhostObjectInterface
    {
        /** @var DocumentMetadata $class */
        $class = $this->manager->getClassMetadata($className);

        $allowedMethods = \array_map(static function (string $field) {
            return \strtolower('get'.$field);
        }, $fields);

        $initializer = function (
            GhostObjectInterface $ghostObject,
            string $method,
            array $parameters,
            &$initializer
        ) use ($fields, $allowedMethods): bool {
            if (('__get' === $method || '__set' === $method) && \in_array($parameters['name'], $fields, true)) {
                return false;
            }

            if (\in_array(\strtolower($method), $allowedMethods, true)) {
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

            if ($field->isStored() && ! \in_array($field->getName(), $fields, true)) {
                continue;
            }

            $reflectionProperty = $field->getReflection();

            if ($reflectionProperty->isPrivate()) {
                $skippedProperties[] = "\0{$field->class}\0{$field->name}";
            } elseif ($reflectionProperty->isProtected()) {
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

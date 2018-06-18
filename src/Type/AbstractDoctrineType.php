<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Type;

use Doctrine\Common\Persistence\ManagerRegistry;

abstract class AbstractDoctrineType extends AbstractType
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function toPHP($value, array $options = [])
    {
        if (null === $value) {
            return null;
        }

        if (! isset($options['class'])) {
            throw new \InvalidArgumentException('Missing object fully qualified name.');
        }

        $om = $this->registry->getManagerForClass($options['class']);

        return $om->find($options['class'], $value['identifier']);
    }

    /**
     * {@inheritdoc}
     */
    public function toDatabase($value, array $options = [])
    {
        if (null === $value) {
            return null;
        }

        if (! isset($options['class'])) {
            throw new \InvalidArgumentException('Missing object fully qualified name.');
        }

        $om = $this->registry->getManagerForClass($options['class']);
        $class = $om->getClassMetadata($options['class']);

        if (1 === count($class->getIdentifier())) {
            $id = array_values($class->getIdentifierValues($value))[0];
        } else {
            $id = $class->getIdentifierValues($value);
        }

        return ['identifier' => $id];
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return static::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getMappingDeclaration(array $options = []): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'identifier' => ['type' => 'keyword'],
            ],
        ];
    }
}

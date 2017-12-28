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
     * @inheritDoc
     */
    public function toPHP($value, array $options = [])
    {
        if (empty($value)) {
            return null;
        }

        if (! isset($options['class'])) {
            throw new \InvalidArgumentException('Missing object fully qualified name.');
        }

        $om = $this->registry->getManagerForClass($options['class']);

        return $om->find($options['class'], $value);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return static::NAME;
    }
}

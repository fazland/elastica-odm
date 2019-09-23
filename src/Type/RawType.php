<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Type;

// TODO: remove me!
final class RawType extends AbstractType
{
    public const NAME = 'raw';

    /**
     * {@inheritdoc}
     */
    public function toPHP($value, array $options = [])
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function toDatabase($value, array $options = [])
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::NAME;
    }

    public function getMappingDeclaration(array $options = []): array
    {
        return [];
    }
}

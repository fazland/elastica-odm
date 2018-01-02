<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Type;

final class IpType extends AbstractType
{
    const NAME = 'ip';

    /**
     * {@inheritdoc}
     */
    public function toPHP($value, array $options = []): ?string
    {
        if (empty($value)) {
            return null;
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function toDatabase($value, array $options = []): ?string
    {
        if (empty($value)) {
            return null;
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getMappingDeclaration(array $options = []): array
    {
        return ['type' => 'ip'];
    }
}

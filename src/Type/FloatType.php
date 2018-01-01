<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Type;

use Fazland\ODM\Elastica\Exception\ConversionFailedException;

final class FloatType extends AbstractType
{
    const NAME = 'float';

    /**
     * {@inheritdoc}
     */
    public function toPHP($value, array $options = []): ?float
    {
        if (null === $value) {
            return null;
        }

        return (float) $value;
    }

    /**
     * {@inheritdoc}
     */
    public function toDatabase($value, array $options = []): ?float
    {
        if (null === $value) {
            return null;
        }

        if (! is_float($value)) {
            throw new ConversionFailedException($value, 'float');
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
        switch ($options['length'] ?? 4) {
            case 4:
                $type = 'float';
                break;

            case 8:
                $type = 'double';
                break;

            default:
                throw new \InvalidArgumentException('Invalid length for float field');
        }

        return ['type' => $type];
    }
}

<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Type;

use Fazland\ODM\Elastica\Exception\ConversionFailedException;

final class LongType extends AbstractType
{
    const NAME = 'long';

    const MAX_VALUE = PHP_INT_MAX;
    const MIN_VALUE = PHP_INT_MIN;

    /**
     * {@inheritdoc}
     */
    public function toPHP($value, array $options = []): ?int
    {
        return $this->doConversion($value);
    }

    /**
     * {@inheritdoc}
     */
    public function toDatabase($value, array $options = []): ?int
    {
        return $this->doConversion($value);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::NAME;
    }

    private function doConversion($value): ?int
    {
        if (null === $value) {
            return null;
        }

        if (! is_int($value) || self::MIN_VALUE > $value || self::MAX_VALUE < $value) {
            throw new ConversionFailedException($value, 'integer between [-' . self::MIN_VALUE . ', ' . self::MAX_VALUE . ']');
        }

        return $value;
    }
}

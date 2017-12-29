<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Type;

use Fazland\ODM\Elastica\Exception\ConversionFailedException;

final class IntegerType extends AbstractType
{
    const NAME = 'integer';

    const MAX_VALUE = 2147483647;
    const MIN_VALUE = -2147483648;

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
            throw new ConversionFailedException($value, 'integer');
        }

        return $value;
    }
}

<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Type;

use Fazland\ODM\Elastica\Exception\ConversionFailedException;

final class ShortType extends AbstractType
{
    const NAME = 'short';

    const MAX_VALUE = 32767;
    const MIN_VALUE = -32768;

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

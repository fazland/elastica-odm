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
        return $this->doConversion($value);

    }

    public function toDatabase($value, array $options = []): ?float
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

    private function doConversion($value): ?float
    {
        if (null === $value) {
            return null;
        }

        if (! is_float($value)) {
            throw new ConversionFailedException($value, 'float');
        }

        return $value;
    }
}

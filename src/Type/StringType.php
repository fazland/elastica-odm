<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Type;

use Fazland\ODM\Elastica\Exception\ConversionFailedException;

final class StringType extends AbstractType
{
    const NAME = 'string';

    /**
     * {@inheritdoc}
     */
    public function toPHP($value, array $options = []): ?string
    {
        return $this->doConversion($value);
    }

    /**
     * {@inheritdoc}
     */
    public function toDatabase($value, array $options = []): ?string
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

    private function doConversion($value): ?string
    {
        if (null === $value) {
            return null;
        }

        if (! is_string($value)) {
            throw new ConversionFailedException($value, 'string');
        }

        return $value;
    }
}

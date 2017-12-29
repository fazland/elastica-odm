<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Type;

use Fazland\ODM\Elastica\Exception\ConversionFailedException;

final class DateTimeType extends AbstractType
{
    const NAME = 'datetime';

    /**
     * {@inheritdoc}
     */
    public function toPHP($value, array $options = [])
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            $value = $value->format(\DateTime::ISO8601);
        }

        return new \DateTime($value);
    }

    /**
     * {@inheritdoc}
     */
    public function toDatabase($value, array $options = [])
    {
        if (empty($value)) {
            return null;
        }

        if (! $value instanceof \DateTimeInterface) {
            throw new ConversionFailedException($value, \DateTimeInterface::class);
        }

        return $value->format($options['format'] ?? \DateTime::ISO8601);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::NAME;
    }
}

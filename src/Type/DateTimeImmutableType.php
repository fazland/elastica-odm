<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Type;

use Fazland\ODM\Elastica\Exception\ConversionFailedException;

final class DateTimeImmutableType extends AbstractType
{
    const NAME = 'datetime_immutable';

    /**
     * {@inheritdoc}
     */
    public function toPHP($value, array $options = [])
    {
        if (empty($value)) {
            return null;
        }

        $format = $options['format'] ?? \DateTime::ISO8601;
        if ($value instanceof \DateTimeInterface) {
            $value = $value->format($format);
        }

        return \DateTimeImmutable::createFromFormat($format, $value);
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

<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Type;

final class DateTimeImmutableType extends AbstractType
{
    const NAME = 'datetime_immutable';

    /**
     * @inheritDoc
     */
    public function toPHP($value, array $options = [])
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            $value = $value->format(\DateTime::ISO8601);
        }

        return new \DateTimeImmutable($value);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return self::NAME;
    }
}

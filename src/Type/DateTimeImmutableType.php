<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Type;

final class DateTimeImmutableType extends AbstractDateTimeType
{
    const NAME = 'datetime_immutable';

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
    protected function getClass(): string
    {
        return \DateTimeImmutable::class;
    }
}

<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Type;

final class DateTimeType extends AbstractDateTimeType
{
    const NAME = 'datetime';

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
        return \DateTime::class;
    }
}

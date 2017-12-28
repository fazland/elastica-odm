<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Type;

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
    public function getName(): string
    {
        return self::NAME;
    }
}

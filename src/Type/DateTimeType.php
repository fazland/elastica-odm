<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Type;

use Cake\Chronos\Chronos;

final class DateTimeType extends AbstractType
{
    const NAME = 'datetime';

    /**
     * @inheritDoc
     */
    public function toPHP($value, array $options = [])
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value;
        }

        return Chronos::parse($value);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return self::NAME;
    }

}
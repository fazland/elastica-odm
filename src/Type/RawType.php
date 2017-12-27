<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Type;

final class RawType extends AbstractType
{
    const NAME = 'raw';

    /**
     * @inheritDoc
     */
    public function toPHP($value, array $options = [])
    {
        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return self::NAME;
    }

}
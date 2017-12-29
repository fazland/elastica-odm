<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Type;

use Fazland\ODM\Elastica\Type\LongType;
use Fazland\ODM\Elastica\Type\TypeInterface;

class LongTypeTest extends AbstractOutOfDomainTest
{
    public function getType(): TypeInterface
    {
        return new LongType();
    }

    public function getValue(): int
    {
        return 123;
    }

    public function getOutOfDomainPositiveValue()
    {
        return PHP_INT_MAX + 10;
    }

    public function getOutOfDomainNegativeValue()
    {
        return PHP_INT_MIN - 10;
    }
}

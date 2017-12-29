<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Type;

use Fazland\ODM\Elastica\Type\IntegerType;
use Fazland\ODM\Elastica\Type\TypeInterface;

class IntegerTypeTest extends AbstractOutOfDomainTest
{
    public function getType(): TypeInterface
    {
        return new IntegerType();
    }

    public function getValue(): int
    {
        return 123;
    }

    public function getOutOfDomainPositiveValue(): int
    {
        if (4 === PHP_INT_SIZE) {
            return PHP_INT_MAX + 10;
        }

        return 3000000000;
    }

    public function getOutOfDomainNegativeValue(): int
    {
        if (4 === PHP_INT_SIZE) {
            return PHP_INT_MIN - 10;
        }

        return -3000000000;
    }
}

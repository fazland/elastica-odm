<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Type;

use Fazland\ODM\Elastica\Type\IntegerType;
use Fazland\ODM\Elastica\Type\TypeInterface;

class IntegerTypeTest extends AbstractPrimitiveTypeTest
{
    public function getType(): TypeInterface
    {
        return new IntegerType();
    }

    public function getValue(): int
    {
        return 123;
    }
}

<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Type;

use Fazland\ODM\Elastica\Type\FloatType;
use Fazland\ODM\Elastica\Type\TypeInterface;

class FloatTypeTest extends AbstractPrimitiveTypeTest
{
    public function getType(): TypeInterface
    {
        return new FloatType();
    }

    public function getValue(): float
    {
        return 456.1;
    }
}

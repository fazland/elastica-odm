<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Type;

use Fazland\ODM\Elastica\Type\BooleanType;
use Fazland\ODM\Elastica\Type\TypeInterface;

class BooleanTypeTest extends AbstractPrimitiveTypeTest
{
    public function getType(): TypeInterface
    {
        return new BooleanType();
    }

    public function getValue(): bool
    {
        return true;
    }
}

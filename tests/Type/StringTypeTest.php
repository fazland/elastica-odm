<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Type;

use Fazland\ODM\Elastica\Type\StringType;
use Fazland\ODM\Elastica\Type\TypeInterface;

class StringTypeTest extends AbstractPrimitiveTypeTest
{
    public function getType(): TypeInterface
    {
        return new StringType();
    }

    public function getValue(): string
    {
        return 'string';
    }
}

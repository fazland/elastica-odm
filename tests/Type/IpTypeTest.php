<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Type;

use Fazland\ODM\Elastica\Type\IpType;
use Fazland\ODM\Elastica\Type\TypeInterface;

class IpTypeTest extends AbstractPrimitiveTypeTest
{
    public function getType(): TypeInterface
    {
        return new IpType();
    }

    public function getValue(): string
    {
        return '192.168.0.1';
    }
}

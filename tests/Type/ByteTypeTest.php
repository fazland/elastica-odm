<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Type;

use Fazland\ODM\Elastica\Type\ByteType;
use Fazland\ODM\Elastica\Type\TypeInterface;

class ByteTypeTest extends AbstractOutOfDomainTest
{
    public function getType(): TypeInterface
    {
        return new ByteType();
    }

    public function getValue(): int
    {
        return 10;
    }

    public function getOutOfDomainPositiveValue(): int
    {
        return 1000;
    }

    public function getOutOfDomainNegativeValue(): int
    {
        return -1000;
    }
}

<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Type;

use Fazland\ODM\Elastica\Type\ShortType;
use Fazland\ODM\Elastica\Type\TypeInterface;

class ShortTypeTest extends AbstractOutOfDomainTest
{
    public function getType(): TypeInterface
    {
        return new ShortType();
    }

    public function getValue(): int
    {
        return 1000;
    }

    public function getOutOfDomainPositiveValue(): int
    {
        return 35000;
    }

    public function getOutOfDomainNegativeValue(): int
    {
        return -35000;
    }
}

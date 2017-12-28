<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Type;

use Fazland\ODM\Elastica\Type\DateTimeType;
use Fazland\ODM\Elastica\Type\TypeInterface;

class DateTimeTypeTest extends AbstractDateTimeTypeTest
{
    public function getType(): TypeInterface
    {
        return new DateTimeType();
    }

    public function getExpectedClass(): string
    {
        return \DateTime::class;
    }
}

<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Type;

use Fazland\ODM\Elastica\Type\DateTimeImmutableType;
use Fazland\ODM\Elastica\Type\TypeInterface;

class DateTimeImmutableTypeTest extends AbstractDateTimeTypeTest
{
    public function getType(): TypeInterface
    {
        return new DateTimeImmutableType();
    }

    public function getExpectedClass(): string
    {
        return \DateTimeImmutable::class;
    }
}

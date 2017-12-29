<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Type;

use PHPUnit\Framework\TestCase;

abstract class AbstractDateTimeTypeTest extends TestCase implements TypeTestInterface
{
    use EmptyValuesTrait;

    abstract public function getExpectedClass(): string;

    public function testToPhpWithDateTimeShouldReturnTheSameInstance(): void
    {
        $type = $this->getType();

        $expectedClass = $this->getExpectedClass();
        $dateTime = $expectedClass::createFromFormat(\DateTime::ISO8601, 'now');

        $this->assertEquals($dateTime, $type->toPHP($dateTime));
    }

    public function testToPhpWithStringValueShouldReturnItsDateTimeRepresentation(): void
    {
        $type = $this->getType();

        $time = '2017-12-29T15:43:00+01:00';
        $expectedClass = $this->getExpectedClass();

        $expected = new $expectedClass($time);

        $this->assertEquals($expected, $type->toPHP($time));
    }
}

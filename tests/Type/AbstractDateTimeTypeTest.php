<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Type;

use Fazland\ODM\Elastica\Type\TypeInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractDateTimeTypeTest extends TestCase
{
    public abstract function getType(): TypeInterface;

    public abstract function getExpectedClass(): string;

    public function emptyValue(): array
    {
        return [
            [''],
            [null],
            [[]],
            [0],
            [0.0],
            ['0'],
            [false],
        ];
    }

    /**
     * @dataProvider emptyValue
     *
     * @param mixed $value
     */
    public function testToPhpWithEmptyValueShouldReturnNull($value): void
    {
        $type = $this->getType();

        $this->assertEquals(null, $type->toPHP($value));
    }

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

        $time = 'tomorrow midnight';
        $expectedClass = $this->getExpectedClass();

        $expected = new $expectedClass($time);

        $this->assertEquals($expected, $type->toPHP($time));
    }
}

<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Type;

use Fazland\ODM\Elastica\Tests\Fixtures\Type\StringableObject;
use Fazland\ODM\Elastica\Type\StringType;
use PHPUnit\Framework\TestCase;

class StringTypeTest extends TestCase
{
    public function testToPhpWithNullValueShouldReturnNull(): void
    {
        $type = new StringType();

        $this->assertEquals(null, $type->toPHP(null));
    }

    /**
     * @expectedException \Fazland\ODM\Elastica\Exception\ConversionFailedException
     */
    public function testToPhpWithNonStringConvertibleValueShouldThrow(): void
    {
        $type = new StringType();

        $type->toPHP([]);
    }

    /**
     * @dataProvider validValues
     *
     * @param mixed $value
     */
    public function testToPhpShouldWork($value): void
    {
        $type = new StringType();

        $this->assertEquals((string) $value, $type->toPHP($value));
    }

    public function validValues(): array
    {
        return [
            ['string'],
            [0],
            [0.0],
            [true],
            [new StringableObject()],
        ];
    }
}
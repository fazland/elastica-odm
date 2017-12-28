<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Type;

trait EmptyValuesTrait
{
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
}

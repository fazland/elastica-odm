<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Type;

use Fazland\ODM\Elastica\Type\TypeInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractPrimitiveTypeTest extends TestCase
{
    public abstract function getType(): TypeInterface;

    public abstract function getValue();

    public function testToPhpWithNullValueShouldReturnNull(): void
    {
        $type = $this->getType();

        $this->assertEquals(null, $type->toPHP(null));
    }

    /**
     * @expectedException \Fazland\ODM\Elastica\Exception\ConversionFailedException
     */
    public function testToPhpWithNonStringValueShouldThrow(): void
    {
        $type = $this->getType();

        $type->toPHP([]);
    }

    public function testToPhpShouldWork(): void
    {
        $type = $this->getType();

        $value = $this->getValue();
        $this->assertEquals($value, $type->toPHP($value));
    }

    public function testToDatabaseWithNullValueShouldReturnNull(): void
    {
        $type = $this->getType();

        $this->assertEquals(null, $type->toDatabase(null));
    }

    /**
     * @expectedException \Fazland\ODM\Elastica\Exception\ConversionFailedException
     */
    public function testToDatabaseWithNonStringValueShouldThrow(): void
    {
        $type = $this->getType();

        $type->toDatabase([]);
    }

    public function testToDatabaseShouldWork(): void
    {
        $type = $this->getType();

        $value = $this->getValue();
        $this->assertEquals($value, $type->toDatabase($value));
    }
}

<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Type;

use Fazland\ODM\Elastica\Type\TypeInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractPrimitiveTypeTest extends TestCase
{
    abstract public function getType(): TypeInterface;

    abstract public function getValue();

    public function testToPhpWithNullValueShouldReturnNull(): void
    {
        $type = $this->getType();

        $this->assertEquals(null, $type->toPHP(null));
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

    public function testToDatabaseShouldWork(): void
    {
        $type = $this->getType();

        $value = $this->getValue();
        $this->assertEquals($value, $type->toDatabase($value));
    }
}

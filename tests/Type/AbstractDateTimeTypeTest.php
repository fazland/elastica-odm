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
        $dateTime = $expectedClass::createFromFormat(\DateTime::ATOM, 'now');

        self::assertEquals($dateTime, $type->toPHP($dateTime));
    }

    public function testToPhpWithStringValueShouldReturnItsDateTimeRepresentation(): void
    {
        $type = $this->getType();

        $time = '2017-12-29T15:43:00+01:00';
        $expectedClass = $this->getExpectedClass();

        $expected = new $expectedClass($time);

        self::assertEquals($expected, $type->toPHP($time));
    }

    public function testDefaultMappingDeclarationShouldHaveIso8601AsFormat(): void
    {
        $type = $this->getType();
        $mapping = $type->getMappingDeclaration();

        self::assertArrayHasKey('format', $mapping);
        self::assertEquals('YYYY-MM-dd\'T\'HH:mm:ssZ', $mapping['format']);
    }
}

<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Type;

use Fazland\ODM\Elastica\Completion;
use Fazland\ODM\Elastica\Type\CompletionType;
use Fazland\ODM\Elastica\Type\TypeInterface;
use PHPUnit\Framework\TestCase;

class CompletionTypeTest extends TestCase
{
    public function testToPhpShouldWork(): void
    {
        $type = $this->getType();
        $value = new Completion();
        $value->input = ['The Beatles', 'Beatles'];

        $this->assertEquals($value, $type->toPHP([
            'input' => ['The Beatles', 'Beatles'],
        ]));
    }

    public function testToPhpWithEmptyValueShouldReturnNull(): void
    {
        $type = $this->getType();

        $this->assertEquals(null, $type->toPHP(null));
    }

    public function testToDatabaseWithNullValueShouldReturnNull(): void
    {
        $type = $this->getType();
        $this->assertEquals(null, $type->toDatabase(null));
    }

    public function testToDatabaseShouldWork(): void
    {
        $type = $this->getType();
        $value = new Completion();
        $value->input = ['The Beatles', 'Beatles'];
        $this->assertEquals([
            'input' => ['The Beatles', 'Beatles'],
        ], $type->toDatabase($value));
    }

    public function getType(): TypeInterface
    {
        return new CompletionType();
    }
}

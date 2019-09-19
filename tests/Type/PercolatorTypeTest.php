<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Type;

use Elastica\Query;
use Elastica\Query\Match;
use Fazland\ODM\Elastica\Type\PercolatorType;
use Fazland\ODM\Elastica\Type\TypeInterface;
use PHPUnit\Framework\TestCase;

class PercolatorTypeTest extends TestCase
{
    public function testToPhpWithEmptyValueShouldReturnNull(): void
    {
        $type = $this->getType();
        self::assertNull($type->toPHP(null));
    }

    public function testToPhpShouldWork(): void
    {
        $type = $this->getType();

        $query = Query::create(['query' => ['match' => ['field' => 'value']]]);
        self::assertEquals($query, $type->toPHP(['match' => ['field' => 'value']]));
    }

    public function testToDatabaseWithNullValueShouldReturnNull(): void
    {
        $type = $this->getType();
        self::assertEquals(null, $type->toDatabase(null));
    }

    public function testToDatabaseShouldWork(): void
    {
        $type = $this->getType();
        $value = new Match('field', 'value');

        self::assertEquals(['match' => ['field' => 'value']], $type->toDatabase($value));
        self::assertEquals(['match' => ['field' => 'value']], $type->toDatabase(Query::create($value)));
    }

    public function getType(): TypeInterface
    {
        return new PercolatorType();
    }
}

<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Type;

use Elastica\Query;
use Elastica\Query\Match;
use Fazland\ODM\Elastica\Type\PercolatorType;
use Fazland\ODM\Elastica\Type\TypeInterface;
use PHPUnit\Framework\TestCase;

class PercolatorTypeTest extends TestCase
{
    use EmptyValuesTrait;

    public function testToPhpShouldWork(): void
    {
        $type = $this->getType();
        $this->assertEquals(['match' => ['field' => 'value']], $type->toPHP(['match' => ['field' => 'value']]));
    }

    public function testToDatabaseWithNullValueShouldReturnNull(): void
    {
        $type = $this->getType();
        $this->assertEquals(null, $type->toDatabase(null));
    }

    public function testToDatabaseShouldWork(): void
    {
        $type = $this->getType();
        $value = new Match('field', 'value');

        $this->assertEquals(['match' => ['field' => 'value']], $type->toDatabase($value));
        $this->assertEquals(['match' => ['field' => 'value']], $type->toDatabase(Query::create($value)));
    }

    public function getType(): TypeInterface
    {
        return new PercolatorType();
    }
}

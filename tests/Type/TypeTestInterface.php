<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Type;

use Fazland\ODM\Elastica\Type\TypeInterface;

interface TypeTestInterface
{
    public function getType(): TypeInterface;
}

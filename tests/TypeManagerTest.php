<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests;

use Fazland\ODM\Elastica\Type\TypeInterface;
use Fazland\ODM\Elastica\Type\TypeManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class TypeManagerTest extends TestCase
{
    /**
     * @expectedException \Fazland\ODM\Elastica\Exception\NoSuchTypeException
     */
    public function testGetTypeShouldThrowOnUnknownType(): void
    {
        $typeManager = new TypeManager();
        $typeManager->getType('unknown type');
    }

    public function testGetTypeShouldWork(): void
    {
        /** @var TypeInterface|ObjectProphecy $type */
        $type = $this->prophesize(TypeInterface::class);
        $type->getName()->willReturn('type_name');

        $typeManager = new TypeManager();
        $typeManager->addType($type->reveal());

        $this->assertEquals($typeManager->getType('type_name'), $type->reveal());
    }
}

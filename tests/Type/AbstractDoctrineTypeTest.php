<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Type;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Fazland\ODM\Elastica\Tests\Fixtures\Type\TestDoctrineType;
use Fazland\ODM\Elastica\Type\TypeInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class AbstractDoctrineTypeTest extends TestCase implements TypeTestInterface
{
    /**
     * @var ManagerRegistry|ObjectProphecy
     */
    private $managerRegistry;

    protected function setUp()
    {
        $this->managerRegistry = $this->prophesize(ManagerRegistry::class);
    }

    public function getType(): TypeInterface
    {
        return new TestDoctrineType($this->managerRegistry->reveal());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testToPhpValueShouldThrowWhenMandatoryClassNameIsMissing(): void
    {
        $type = $this->getType();

        $value = 'i_am_a_value';

        $type->toPHP($value);
    }

    public function testToPhpWithEmptyValueShouldReturnNull(): void
    {
        $type = $this->getType();
        self::assertNull($type->toPHP(null));
    }

    public function testToPhpValueShouldFindTheDesiredDocument(): void
    {
        $type = $this->getType();

        $value = ['identifier' => 'identifier'];

        $fqcn = 'Fully\\Qualified\\Class\\Name';

        /** @var ObjectManager|ObjectProphecy $manager */
        $manager = $this->prophesize(ObjectManager::class);
        $this->managerRegistry->getManagerForClass($fqcn)->willReturn($manager);

        $manager->find($fqcn, 'identifier')->shouldBeCalled();

        $type->toPHP($value, ['class' => $fqcn]);
    }
}

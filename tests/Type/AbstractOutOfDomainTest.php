<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Type;

abstract class AbstractOutOfDomainTest extends AbstractPrimitiveTypeTest
{
    abstract public function getOutOfDomainPositiveValue();

    abstract public function getOutOfDomainNegativeValue();

    /**
     * @expectedException \Fazland\ODM\Elastica\Exception\ConversionFailedException
     */
    public function testToPhpShouldThrowOnNonBytePositiveValue(): void
    {
        $this->getType()->toPHP($this->getOutOfDomainPositiveValue());
    }

    /**
     * @expectedException \Fazland\ODM\Elastica\Exception\ConversionFailedException
     */
    public function testToPhpShouldThrowOnNonByteNegativeValue(): void
    {
        $this->getType()->toPHP($this->getOutOfDomainNegativeValue());
    }

    /**
     * @expectedException \Fazland\ODM\Elastica\Exception\ConversionFailedException
     */
    public function testToDatabaseShouldThrowOnNonBytePositiveValue(): void
    {
        $this->getType()->toDatabase($this->getOutOfDomainPositiveValue());
    }

    /**
     * @expectedException \Fazland\ODM\Elastica\Exception\ConversionFailedException
     */
    public function testToDatabaseShouldThrowOnNonByteNegativeValue(): void
    {
        $this->getType()->toDatabase($this->getOutOfDomainNegativeValue());
    }
}

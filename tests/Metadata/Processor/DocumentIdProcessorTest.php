<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Metadata\Processor;

use Fazland\ODM\Elastica\Annotation\DocumentId;
use Fazland\ODM\Elastica\Metadata\DocumentMetadata;
use Fazland\ODM\Elastica\Metadata\FieldMetadata;
use Fazland\ODM\Elastica\Metadata\Processor\DocumentIdProcessor;
use Fazland\ODM\Elastica\Tests\Fixtures\Document\Foo;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class DocumentIdProcessorTest extends TestCase
{
    /**
     * @var DocumentIdProcessor
     */
    private $processor;

    /**
     * @var DocumentMetadata|ObjectProphecy
     */
    private $documentMetadata;

    protected function setUp()
    {
        $this->processor = new DocumentIdProcessor();
        $this->documentMetadata = new DocumentMetadata(new \ReflectionClass(Foo::class));
    }

    public function testProcessStrategyAuto()
    {
        $metadata = new FieldMetadata($this->documentMetadata, Foo::class);

        $subject = new DocumentId();
        $subject->strategy = 'auto';

        $this->processor->process($metadata, $subject);

        $this->assertTrue($metadata->identifier);
        $this->assertEquals(DocumentMetadata::GENERATOR_TYPE_AUTO, $this->documentMetadata->idGeneratorType);
    }

    public function testProcessStrategyNone()
    {
        $metadata = new FieldMetadata($this->documentMetadata, Foo::class);

        $subject = new DocumentId();
        $subject->strategy = 'none';

        $this->processor->process($metadata, $subject);

        $this->assertTrue($metadata->identifier);
        $this->assertEquals(DocumentMetadata::GENERATOR_TYPE_NONE, $this->documentMetadata->idGeneratorType);
    }
}

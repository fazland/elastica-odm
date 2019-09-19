<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Hydrator;

use Doctrine\Common\EventManager;
use Elastica\Document;
use Elastica\Exception\InvalidException;
use Elastica\Query;
use Elastica\Response;
use Elastica\Result;
use Elastica\ResultSet;
use Fazland\ODM\Elastica\DocumentManagerInterface;
use Fazland\ODM\Elastica\Hydrator\ObjectHydrator;
use Fazland\ODM\Elastica\Metadata\DocumentMetadata;
use Fazland\ODM\Elastica\Metadata\FieldMetadata;
use Fazland\ODM\Elastica\Tests\Fixtures\Hydrator\TestDocument;
use Fazland\ODM\Elastica\Type\StringType;
use Fazland\ODM\Elastica\Type\TypeManager;
use Fazland\ODM\Elastica\UnitOfWork;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class ObjectHydratorTest extends TestCase
{
    /**
     * @var EventManager|ObjectProphecy
     */
    private $eventManager;

    /**
     * @var TypeManager
     */
    private $typeManager;

    /**
     * @var DocumentManagerInterface|ObjectProphecy
     */
    private $documentManager;

    /**
     * @var UnitOfWork
     */
    private $uow;

    /**
     * @var ObjectHydrator
     */
    private $hydrator;

    protected function setUp(): void
    {
        $stringType = new StringType();

        $this->eventManager = $this->prophesize(EventManager::class);
        $this->typeManager = new TypeManager();
        $this->typeManager->addType($stringType);

        $this->documentManager = $this->prophesize(DocumentManagerInterface::class);
        $this->documentManager->getEventManager()->willReturn($this->eventManager);
        $this->documentManager->getTypeManager()->willReturn($this->typeManager);

        $this->uow = new UnitOfWork($this->documentManager->reveal());
        $this->documentManager->getUnitOfWork()->willReturn($this->uow);

        $this->hydrator = new ObjectHydrator($this->documentManager->reveal());
    }

    public function testHydrateOneShouldWork(): void
    {
        $class = $this->getTestDocumentMetadata();
        $this->documentManager->getClassMetadata(TestDocument::class)->willReturn($class);

        $documentId = '12345';
        $expectedDocumentValues = [
            'id' => $documentId,
            'field1' => 'field1',
            'field2' => 'field2',
        ];
        $document = $this->prophesize(Document::class);
        $document->getId()->willReturn($documentId);
        $document->getData()->willReturn($expectedDocumentValues);

        $result = $this->hydrator->hydrateOne($document->reveal(), TestDocument::class);
        $this->assertTestDocumentEquals($expectedDocumentValues, $result);
    }

    public function testHydrateAllShouldReturnEmptyArrayOnEmptyResultSet(): void
    {
        $resultSet = $this->prophesize(ResultSet::class);
        $resultSet->count()->willReturn(0);

        self::assertEmpty($this->hydrator->hydrateAll($resultSet->reveal(), TestDocument::class));
    }

    public function testHydrateAllShouldWork(): void
    {
        $query = $this->prophesize(Query::class);
        $query->getParam('_source')->willThrow(InvalidException::class);

        $result1 = $this->prophesize(Result::class);
        $result2 = $this->prophesize(Result::class);

        $results = [$result1->reveal(), $result2->reveal()];
        $resultSet = new ResultSet($this->prophesize(Response::class)->reveal(), $query->reveal(), $results);

        $document1Id = '12345';
        $expectedDocument1Values = [
            'id' => $document1Id,
            'field1' => 'document1.field1',
            'field2' => 'document1.field2',
        ];
        $document1 = $this->prophesize(Document::class);
        $document1->getId()->willReturn($document1Id);
        $document1->getData()->willReturn($expectedDocument1Values);

        $document2Id = '67890';
        $expectedDocument2Values = [
            'id' => $document2Id,
            'field1' => 'document2.field1',
            'field2' => 'document2.field2',
        ];
        $document2 = $this->prophesize(Document::class);
        $document2->getId()->willReturn($document2Id);
        $document2->getData()->willReturn($expectedDocument2Values);

        $result1->getDocument()->willReturn($document1);
        $result2->getDocument()->willReturn($document2);

        $class = $this->getTestDocumentMetadata();

        $this->documentManager->getClassMetadata(TestDocument::class)->willReturn($class);

        /** @var TestDocument[] $documents */
        $documents = $this->hydrator->hydrateAll($resultSet, TestDocument::class);

        self::assertCount(2, $documents);
        $this->assertTestDocumentEquals($expectedDocument1Values, $documents[0]);
        $this->assertTestDocumentEquals($expectedDocument2Values, $documents[1]);
    }

    public function getTestDocumentMetadata(): DocumentMetadata
    {
        $class = new DocumentMetadata(new \ReflectionClass(TestDocument::class));
        $id = new FieldMetadata($class, 'id');
        $id->identifier = true;
        $id->type = 'string';
        $id->fieldName = 'id';
        $class->identifier = $id;

        $field1 = new FieldMetadata($class, 'field1');
        $field1->type = 'string';
        $field1->fieldName = 'field1';

        $field2 = new FieldMetadata($class, 'field2');
        $field2->type = 'string';
        $field2->fieldName = 'field2';

        $class->addAttributeMetadata($id);
        $class->addAttributeMetadata($field1);
        $class->addAttributeMetadata($field2);

        return $class;
    }

    private function assertTestDocumentEquals(array $expectedValues, TestDocument $document)
    {
        self::assertEquals($expectedValues['id'], $document->getId());
        self::assertEquals($expectedValues['field1'], $document->getField1());
        self::assertEquals($expectedValues['field2'], $document->getField2());
    }
}

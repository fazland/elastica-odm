<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests;

use Fazland\ODM\Elastica\DocumentManager;
use Fazland\ODM\Elastica\Tests\Fixtures\Document\Foo;
use Fazland\ODM\Elastica\Tests\Traits\DocumentManagerTestTrait;
use Fazland\ODM\Elastica\Tests\Traits\FixturesTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * @group functional
 */
class DocumentManagerTest extends TestCase
{
    use DocumentManagerTestTrait;
    use FixturesTestTrait;

    /**
     * @var DocumentManager
     */
    private $dm;

    public static function setUpBeforeClass()
    {
        self::resetFixtures(self::createDocumentManager());
    }

    protected function setUp()
    {
        $this->dm = $this->createDocumentManager();
    }

    public function testFindShouldReturnNullIfNoDocumentIsFound()
    {
        $this->assertNull($this->dm->find(Foo::class, 'non-existent'));
    }

    public function testFindShouldReturnAnObject()
    {
        $result = $this->dm->find(Foo::class, 'foo_test_document');
        $this->assertInstanceOf(Foo::class, $result);

        $result2 = $this->dm->find(Foo::class, 'foo_test_document');
        $this->assertEquals(spl_object_hash($result), spl_object_hash($result2));
    }

    public function testPersistAndFlush()
    {
        $document = new Foo();
        $document->id = 'test_persist_and_flush';
        $document->stringField = 'footest_string';

        $this->dm->persist($document);
        $this->dm->flush();

        $result = $this->dm->find(Foo::class, 'test_persist_and_flush');
        $this->assertInstanceOf(Foo::class, $result);
        $this->assertEquals(spl_object_hash($document), spl_object_hash($result));

        $this->dm->clear();

        $result = $this->dm->find(Foo::class, 'test_persist_and_flush');
        $this->assertInstanceOf(Foo::class, $result);
        $this->assertEquals('footest_string', $document->stringField);
    }

    public function testUpdateAndFlush()
    {
        $document = $this->dm->find(Foo::class, 'foo_test_document');
        $this->assertInstanceOf(Foo::class, $document);

        $document->stringField = 'test_string_field';
        $this->dm->flush();

        $this->dm->clear();

        $result = $this->dm->find(Foo::class, 'foo_test_document');
        $this->assertEquals('test_string_field', $result->stringField);
    }
}

<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Command;

use Fazland\ODM\Elastica\Command\DropSchemaCommand;
use Fazland\ODM\Elastica\Tests\Traits\DocumentManagerTestTrait;
use Fazland\ODM\Elastica\Tests\Traits\FixturesTestTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class DropSchemaCommandTest extends TestCase
{
    use DocumentManagerTestTrait;
    use FixturesTestTrait;

    public function testShouldDropIndexesSuccessfully(): void
    {
        self::resetFixtures($dm = self::createDocumentManager());
        if (\version_compare($dm->getDatabase()->getConnection()->getVersion(), '6.0.0', '<')) {
            self::markTestSkipped('Deletion of aliases is rejected only from ES 6.0');
        }

        $tester = new CommandTester(new DropSchemaCommand($dm));

        $tester->execute([], ['interactive' => false]);
        self::assertEquals(<<<CMDLINE

Elastica ODM - drop schema
==========================

 ! [CAUTION] This operation will drop all the indices defined in your mapping.

 [WARNING] foo_with_aliases_index/foo_type is an alias.

           Pass --with-aliases option to drop the alias too.

 [OK] All done.


CMDLINE
, implode("\n", array_map('rtrim', explode("\n", $tester->getDisplay(true)))));
    }

    public function testShouldDropIndexesAndAliasesSuccessfully(): void
    {
        self::resetFixtures($dm = self::createDocumentManager());
        $tester = new CommandTester(new DropSchemaCommand($dm));

        $tester->execute(['--with-aliases' => true], ['interactive' => false]);
        self::assertEquals(<<<CMDLINE

Elastica ODM - drop schema
==========================

 ! [CAUTION] This operation will drop all the indices defined in your mapping.

 [OK] All done.


CMDLINE
, implode("\n", array_map('rtrim', explode("\n", $tester->getDisplay(true)))));
    }
}

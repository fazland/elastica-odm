<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests;

use Fazland\ODM\Elastica\Client;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testGetIndexWithAliasShouldReturnTheSameIndex():void
    {
        $client = new Client();

        $index = 'index';
        $alias1 = 'alias1';
        $alias2 = 'alias2';

        $client->addAlias($alias1, $index);
        $client->addAlias($alias2, $index);

        $this->assertEquals($client->getIndex($alias1), $client->getIndex($alias2));
    }
}

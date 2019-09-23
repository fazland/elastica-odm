<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\VarDumper;

use ProxyManager\Proxy\ProxyInterface;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Test\VarDumperTestTrait as BaseTrait;

trait VarDumperTestTrait
{
    use BaseTrait;

    protected function getDump($data, $key = null, $filter = 0)
    {
        $flags = \getenv('DUMP_LIGHT_ARRAY') ? CliDumper::DUMP_LIGHT_ARRAY : 0;
        $flags |= \getenv('DUMP_STRING_LENGTH') ? CliDumper::DUMP_STRING_LENGTH : 0;
        $flags |= \getenv('DUMP_COMMA_SEPARATOR') ? CliDumper::DUMP_COMMA_SEPARATOR : 0;

        $cloner = new VarCloner();
        $cloner->addCasters([ProxyInterface::class => ProxyCaster::class.'::castProxy']);
        $cloner->setMaxItems(-1);

        $dumper = new CliDumper(null, null, $flags);
        $dumper->setColors(false);
        $data = $cloner->cloneVar($data, $filter)->withRefHandles(false);
        if (null !== $key && null === $data = $data->seek($key)) {
            return null;
        }

        return \rtrim($dumper->dump($data, true));
    }
}

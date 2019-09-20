<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\VarDumper;

use ProxyManager\Proxy\ProxyInterface;
use Symfony\Component\VarDumper\Cloner\Stub;

final class ProxyCaster
{
    public static function castProxy(ProxyInterface $proxy, array $a, Stub $stub, bool $isNested): array
    {
        $stub->class = \get_parent_class($proxy).' (proxy)';
        $prefix = "\0".\get_class($proxy)."\0";
        foreach ($a as $key => $value) {
            if (0 === \strpos($key, $prefix.'initializationTracker') || 0 === \strpos($key, $prefix.'initializer')) {
                unset($a[$key]);
            }
        }

        return $a;
    }
}

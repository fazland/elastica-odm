<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Util;

use Doctrine\Common\Persistence\Proxy;
use ProxyManager\Proxy\ProxyInterface;

final class ClassUtil
{
    private function __construct()
    {
        // Cannot be instantiated.
    }

    /**
     * Gets the object "real" class.
     *
     * @param object $object
     *
     * @return string
     */
    public static function getClass($object): string
    {
        if (! \is_object($object)) {
            throw new \TypeError(\sprintf('Argument 1 passed to '.__METHOD__.' should be an object. %s passed', gettype($object)));
        }

        $class = \get_class($object);
        if ($object instanceof ProxyInterface || $object instanceof Proxy || false !== \strpos($class, '\\__PM__\\')) {
            $class = \get_parent_class($object);
        }

        return $class;
    }
}

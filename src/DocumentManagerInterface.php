<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica;

use Fazland\ODM\Elastica\Type\TypeManager;
use Doctrine\Common\Persistence\ObjectManager;
use ProxyManager\Factory\LazyLoadingGhostFactory;

interface DocumentManagerInterface extends ObjectManager
{
    /**
     * Returns the proxy factory used by this document manager.
     * See ocramius/proxy-manager for more info on how to use it.
     *
     * @return LazyLoadingGhostFactory
     */
    public function getProxyFactory(): LazyLoadingGhostFactory;

    /**
     * Gets the type manager used in this manager.
     * It must be used to register types converters.
     *
     * @return TypeManager
     */
    public function getTypeManager(): TypeManager;

    /**
     * Gets the current unit of work.
     * Holds currently active (attached) documents.
     *
     * @return UnitOfWork
     */
    public function getUnitOfWork(): UnitOfWork;
}

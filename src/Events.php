<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica;

final class Events
{
    /**
     * The onClear event occurs when the DocumentManager#clear() operation is invoked,
     * after all references to documents have been removed from the unit of work.
     *
     * @var string
     */
    const onClear = 'onClear';

    /**
     * The preFlush event occurs when the DocumentManager#flush() operation is invoked,
     * but before any changes to managed documents have been calculated.
     *
     * @var string
     */
    const preFlush = 'preFlush';
}

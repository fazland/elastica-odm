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
}

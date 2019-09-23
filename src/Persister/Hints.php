<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Persister;

final class Hints
{
    /**
     * If set to true, indicates that a refresh operation is in progress.
     */
    public const HINT_REFRESH = 'elastica.refresh';
}

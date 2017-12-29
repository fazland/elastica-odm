<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Id;

abstract class AbstractIdGenerator implements GeneratorInterface
{
    /**
     * @inheritdoc
     */
    public function isPostInsertGenerator(): bool
    {
        return false;
    }
}
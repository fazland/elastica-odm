<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Metadata\Loader;

use Kcs\Metadata\Loader\LoaderInterface as BaseLoaderInterface;

interface LoaderInterface extends BaseLoaderInterface
{
    public function getAllClassNames(): array;
}

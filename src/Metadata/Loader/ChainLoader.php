<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Metadata\Loader;

use Kcs\Metadata\ClassMetadataInterface;

class ChainLoader implements LoaderInterface
{
    /**
     * @var LoaderInterface[]
     */
    private $loaders;

    public function __construct(array $loaders)
    {
        $this->loaders = (function (...$loaders) {
            return $loaders;
        })(...$loaders);
    }

    public function addLoader(LoaderInterface $loader): void
    {
        $this->loaders[] = $loader;
    }

    public function getAllClassNames(): array
    {
        $classes = [];
        foreach ($this->loaders as $loader) {
            $classes = array_merge($classes, $loader->getAllClassNames());
        }

        return array_unique($classes);
    }

    /**
     * {@inheritdoc}
     */
    public function loadClassMetadata(ClassMetadataInterface $classMetadata): bool
    {
        $success = false;

        foreach ($this->loaders as $loader) {
            $success = $loader->loadClassMetadata($classMetadata) || $success;
        }

        return $success;
    }
}

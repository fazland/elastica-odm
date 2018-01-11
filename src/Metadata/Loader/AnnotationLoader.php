<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Metadata\Loader;

use Fazland\ODM\Elastica\Annotation\Document;
use Fazland\ODM\Elastica\Metadata\FieldMetadata;
use Kcs\ClassFinder\Finder\RecursiveFinder;
use Kcs\Metadata\ClassMetadataInterface;
use Kcs\Metadata\Loader\AnnotationProcessorLoader;
use Kcs\Metadata\Loader\Processor\ProcessorFactoryInterface;

class AnnotationLoader extends AnnotationProcessorLoader implements LoaderInterface
{
    /**
     * @var string
     */
    private $prefixDir;

    public function __construct(ProcessorFactoryInterface $processorFactory, string $prefixDir)
    {
        $this->prefixDir = $prefixDir;

        parent::__construct($processorFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function loadClassMetadata(ClassMetadataInterface $classMetadata): bool
    {
        $reflectionClass = $classMetadata->getReflectionClass();
        $this->processClassDescriptors($classMetadata, $this->getClassDescriptors($reflectionClass));

        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $attributeMetadata = $this->createMethodMetadata($reflectionMethod);
            $this->processMethodDescriptors($attributeMetadata, $this->getMethodDescriptors($reflectionMethod));

            $classMetadata->addAttributeMetadata($attributeMetadata);
        }

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $attributeMetadata = new FieldMetadata($classMetadata, $reflectionProperty->name);
            $this->processPropertyDescriptors($attributeMetadata, $this->getPropertyDescriptors($reflectionProperty));

            $classMetadata->addAttributeMetadata($attributeMetadata);
        }

        return true;
    }

    public function getAllClassNames(): array
    {
        $finder = new RecursiveFinder($this->prefixDir);
        $finder->annotatedBy(Document::class);

        $classes = [];
        foreach ($finder as $className => $reflection) {
            if (! $reflection->isInstantiable()) {
                continue;
            }

            $classes[] = $className;
        }

        return $classes;
    }
}

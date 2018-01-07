<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Metadata\Loader;

use Fazland\ODM\Elastica\Metadata\FieldMetadata;
use Kcs\Metadata\ClassMetadataInterface;
use Kcs\Metadata\Loader\AnnotationProcessorLoader;
use Kcs\Metadata\Loader\Processor\ProcessorFactoryInterface;

class AnnotationLoader extends AnnotationProcessorLoader implements LoaderInterface
{
    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $prefixDir;

    public function __construct(ProcessorFactoryInterface $processorFactory, string $namespace, string $prefixDir)
    {
        $this->namespace = $namespace;
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
        $iterator = new \RegexIterator(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->prefixDir, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::LEAVES_ONLY
            ),
            '/^.+\.php$/i',
            \RecursiveRegexIterator::GET_MATCH
        );

        $included_files = [];
        foreach ($iterator as $match) {
            $path = $match[0];
            if ( ! preg_match('(^phar:)i', $path)) {
                $path = realpath($path);
            }

            require_once $path;
            $included_files[] = $path;
        }

        $classes = [];
        foreach (get_declared_classes() as $className) {
            $reflClass = new \ReflectionClass($className);
            if (in_array($reflClass->getFileName(), $included_files)) {
                $classes[] = $className;
            }
        }

        return $classes;
    }
}

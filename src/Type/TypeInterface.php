<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Type;

use Fazland\ODM\Elastica\Exception\ConversionFailedException;

interface TypeInterface
{
    /**
     * Converts the db stored value to PHP type.
     *
     * @param mixed $value
     * @param array $options
     *
     * @return mixed
     */
    public function toPHP($value, array $options = []);

    /**
     * Converts the PHP value to the target database type.
     *
     * @param mixed $value
     * @param array $options
     *
     * @return mixed
     *
     * @throws ConversionFailedException if conversion fails
     */
    public function toDatabase($value, array $options = []);

    /**
     * Returns the name of this type.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Gets the mapping type for the current field type.
     *
     * @param array $options
     *
     * @return array
     */
    public function getMappingDeclaration(array $options = []): array;
}

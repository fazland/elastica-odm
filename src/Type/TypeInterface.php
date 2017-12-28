<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Type;

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
     * Returns the name of this type.
     *
     * @return string
     */
    public function getName(): string;
}

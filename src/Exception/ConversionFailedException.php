<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Exception;

class ConversionFailedException extends \Exception implements ExceptionInterface
{
    public function __construct($value, $expected)
    {
        $expected = is_array($expected) ? $expected : [$expected];
        $given = is_object($value) ? get_class($value) : gettype($value);

        parent::__construct('Conversion failed. Expected '.implode(' or ', $expected).', but '.$given.' was given');
    }
}

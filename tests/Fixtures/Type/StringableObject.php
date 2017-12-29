<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Fixtures\Type;

class StringableObject
{
    public function __toString(): string
    {
        return 'i_can_be_converted_to_string';
    }
}

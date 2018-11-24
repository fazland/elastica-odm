<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica;

use Elastica\ArrayableInterface;

final class Completion implements ArrayableInterface
{
    /**
     * @var string|string[]
     */
    public $input;

    /**
     * @var int
     */
    public $weight;

    public function toArray()
    {
        return [
            'input' => $this->input,
            'weight' => $this->weight,
        ];
    }
}

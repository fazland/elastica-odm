<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Fixtures\Hydrator;

class TestDocument
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $field1;

    /**
     * @var string
     */
    private $field2;

    public function __construct(string $field1, string $field2)
    {
        $this->id = (string) mt_rand(0, 99999);
        $this->field1 = $field1;
        $this->field2 = $field2;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getField1(): string
    {
        return $this->field1;
    }

    public function getField2(): string
    {
        return $this->field2;
    }
}

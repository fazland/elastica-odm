<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Type;

use Fazland\ODM\Elastica\Exception\NoSuchTypeException;

final class TypeManager
{
    /**
     * @var TypeInterface[]
     */
    private $types;

    public function __construct()
    {
        $this->types = [];
    }

    public function addType(TypeInterface $type): void
    {
        $this->types[ $type->getName() ] = $type;
    }

    public function getType(string $type): TypeInterface
    {
        if (! isset($this->types[$type])) {
            throw new NoSuchTypeException('No such type "' . $type . '"');
        }

        return $this->types[$type];
    }
}
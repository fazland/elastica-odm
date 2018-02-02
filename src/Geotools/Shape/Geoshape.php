<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Geotools\Shape;

use Fazland\ODM\Elastica\Geotools\Coordinate\Coordinate;

abstract class Geoshape implements GeoshapeInterface
{
    public static function fromArray(array $shape)
    {
        $type = $shape['type'] ?? null;

        switch ($type) {
            case 'point':
                return new Point(new Coordinate($shape['coordinates']));

            case 'circle':
                return new Circle(new Coordinate($shape['coordinates']), $shape['radius']);

            case 'linestring':
                return new Linestring(...array_map(Coordinate::class.'::create', $shape['coordinates']));

            default:
                throw new \InvalidArgumentException('Unknown geoshape type "'.($type ?? 'null').'"');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}

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
                return new Linestring(...\array_map(Coordinate::class.'::create', $shape['coordinates']));

            case 'polygon':
                return self::createPolygon($shape['coordinates']);

            case 'multipolygon':
                return new MultiPolygon(...\array_map(__CLASS__.'::createPolygon', $shape['coordinates']));

            case 'geometrycollection':
                return new GeometryCollection(...\array_map(__CLASS__.'::fromArray', $shape['geometries']));

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

    /**
     * Creates a Polygon object from coordinates array.
     *
     * @param array $coordinates
     *
     * @return Polygon
     */
    private static function createPolygon(array $coordinates): Polygon
    {
        $polygon = \array_shift($coordinates);

        return new Polygon(\array_map(Coordinate::class.'::create', $polygon), ...\array_map(static function (array $poly) {
            return \array_map(Coordinate::class.'::create', $poly);
        }, $coordinates));
    }
}

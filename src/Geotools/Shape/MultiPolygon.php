<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Geotools\Shape;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Represents a multipolygon geo_shape.
 */
final class MultiPolygon extends Geoshape
{
    /**
     * @var Collection|Polygon[]
     */
    private $polygons;

    public function __construct(Polygon ...$polygons)
    {
        $this->polygons = new ArrayCollection($polygons);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return [
            'type' => 'multipolygon',
            'coordinates' => $this->polygons->map(static function (Polygon $polygon) {
                return $polygon->toArray();
            })->toArray(),
        ];
    }
}

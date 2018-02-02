<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Geotools\Shape;

use Fazland\ODM\Elastica\Geotools\Coordinate\CoordinateInterface;

/**
 * Represents a point geo_shape.
 */
final class Point extends Geoshape
{
    /**
     * @var CoordinateInterface
     */
    private $coordinate;

    public function __construct(CoordinateInterface $coordinate)
    {
        $this->coordinate = $coordinate;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return [
            'type' => 'point',
            'coordinates' => $this->coordinate->jsonSerialize(),
        ];
    }
}

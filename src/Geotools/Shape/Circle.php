<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Geotools\Shape;

use Fazland\ODM\Elastica\Geotools\Coordinate\CoordinateInterface;

/**
 * Represents a circle geo_shape.
 */
final class Circle extends Geoshape
{
    /**
     * @var CoordinateInterface
     */
    private $center;

    /**
     * @var string
     */
    private $radius;

    public function __construct(CoordinateInterface $center, string $radius)
    {
        $this->center = $center;
        $this->radius = $radius;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return [
            'type' => 'linestring',
            'coordinates' => $this->center->jsonSerialize(),
            'radius' => $this->radius,
        ];
    }
}

<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Geotools\Shape;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Fazland\ODM\Elastica\Geotools\Coordinate\CoordinateInterface;

/**
 * Represents a polygon geo_shape.
 */
final class Polygon extends Geoshape
{
    /**
     * @var Collection|CoordinateInterface[]
     */
    private $outer;

    /**
     * @var Collection[]|CoordinateInterface[][]
     */
    private $holes;

    public function __construct(array $outer, array ...$holes)
    {
        $normalize = static function (CoordinateInterface ...$coordinate) {
            return $coordinate;
        };

        $this->outer = new ArrayCollection($normalize(...$outer));
        $this->holes = \array_map(static function (array $hole) use ($normalize) {
            return new ArrayCollection($normalize(...$hole));
        }, $holes);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $serialize = function (CoordinateInterface $coordinate) {
            return $coordinate->jsonSerialize();
        };

        $coordinates = [$this->outer->map($serialize)->toArray()];
        foreach ($this->holes as $hole) {
            $coordinates[] = $hole->map($serialize)->toArray();
        }

        return [
            'type' => 'polygon',
            'coordinates' => $coordinates,
        ];
    }
}

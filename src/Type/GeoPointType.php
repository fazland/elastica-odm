<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Type;

use Fazland\ODM\Elastica\Exception\ConversionFailedException;
use Fazland\ODM\Elastica\Geotools\Coordinate\Coordinate;
use Fazland\ODM\Elastica\Geotools\Coordinate\CoordinateInterface;
use Fazland\ODM\Elastica\Geotools\Geohash\Geohash;

final class GeoPointType extends AbstractType
{
    const NAME = 'geo_point';

    /**
     * {@inheritdoc}
     */
    public function toPHP($value, array $options = []): ?CoordinateInterface
    {
        if (empty($value)) {
            return null;
        }

        if (\is_array($value)) {
            if (isset($value['lat'], $value['lon'])) {
                $lat = $value['lat'];
                $lon = $value['lon'];
            } else {
                $lat = $value[1];
                $lon = $value[0];
            }

            return new Coordinate([(float) $lat, (float) $lon]);
        } elseif (\is_string($value)) {
            if (false === \strpos($value, ',')) {
                return (new Geohash($value))->getCoordinate();
            }

            return new Coordinate($value);
        }

        throw new ConversionFailedException($value, 'geo point');
    }

    /**
     * {@inheritdoc}
     */
    public function toDatabase($value, array $options = []): ?array
    {
        if (empty($value)) {
            return null;
        }

        if (! $value instanceof CoordinateInterface) {
            throw new ConversionFailedException($value, CoordinateInterface::class);
        }

        return $value->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getMappingDeclaration(array $options = []): array
    {
        return ['type' => 'geo_point'];
    }
}

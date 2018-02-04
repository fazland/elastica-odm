<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Type;

use Fazland\ODM\Elastica\Exception\ConversionFailedException;
use Fazland\ODM\Elastica\Geotools\Coordinate\CoordinateInterface;
use Fazland\ODM\Elastica\Geotools\Shape\Geoshape;

final class GeoShapeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function toPHP($value, array $options = []): ?Geoshape
    {
        if (empty($value)) {
            return null;
        }

        return Geoshape::fromArray($value);
    }

    /**
     * {@inheritdoc}
     */
    public function toDatabase($value, array $options = []): ?array
    {
        if (empty($value)) {
            return null;
        }

        if (! $value instanceof Geoshape) {
            throw new ConversionFailedException($value, Geoshape::class);
        }

        return $value->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'geo_shape';
    }

    /**
     * {@inheritdoc}
     */
    public function getMappingDeclaration(array $options = []): array
    {
        return ['type' => 'geo_shape'];
    }
}

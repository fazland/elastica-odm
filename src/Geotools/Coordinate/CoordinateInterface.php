<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Geotools\Coordinate;

use Elastica\ArrayableInterface;

interface CoordinateInterface extends ArrayableInterface, \JsonSerializable
{
    /**
     * Normalizes a latitude to the (-90, 90) range.
     * Latitudes below -90.0 or above 90.0 degrees are capped, not wrapped.
     *
     * @param float $latitude The latitude to normalize
     *
     * @return float
     */
    public function normalizeLatitude(float $latitude): float;

    /**
     * Normalizes a longitude to the (-180, 180) range.
     * Longitudes below -180.0 or abode 180.0 degrees are wrapped.
     *
     * @param float $longitude The longitude to normalize
     *
     * @return float
     */
    public function normalizeLongitude(float $longitude): float;

    /**
     * Set the latitude.
     *
     * @param float $latitude
     */
    public function setLatitude(float $latitude): void;

    /**
     * Get the latitude.
     *
     * @return float
     */
    public function getLatitude(): float;

    /**
     * Set the longitude.
     *
     * @param float $longitude
     */
    public function setLongitude(float $longitude): void;

    /**
     * Get the longitude.
     *
     * @return float
     */
    public function getLongitude(): float;
}

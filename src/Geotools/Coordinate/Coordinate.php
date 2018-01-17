<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Geotools\Coordinate;

class Coordinate implements CoordinateInterface, \JsonSerializable
{
    /**
     * The latitude of the coordinate.
     *
     * @var float
     */
    private $latitude;

    /**
     * The longitude of the coordinate.
     *
     * @var float
     */
    private $longitude;

    /**
     * Set the latitude and the longitude of the coordinates into an selected ellipsoid.
     *
     * @param array|string $coordinates the coordinates
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($coordinates)
    {
        if (is_array($coordinates) && 2 === count($coordinates)) {
            $this->setLatitude($coordinates[0]);
            $this->setLongitude($coordinates[1]);
        } elseif (is_string($coordinates)) {
            $this->setFromString($coordinates);
        } else {
            throw new \InvalidArgumentException('It should be a string or an array');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function normalizeLatitude(float $latitude): float
    {
        return (float) max(-90, min(90, $latitude));
    }

    /**
     * {@inheritdoc}
     */
    public function normalizeLongitude(float $longitude): float
    {
        if (180 === $longitude % 360) {
            return 180.0;
        }

        $mod = fmod($longitude, 360);
        $longitude = $mod < -180 ? $mod + 360 : ($mod > 180 ? $mod - 360 : $mod);

        return (float) $longitude;
    }

    /**
     * {@inheritdoc}
     */
    public function setLatitude(float $latitude): void
    {
        $this->latitude = $this->normalizeLatitude($latitude);
    }

    /**
     * {@inheritdoc}
     */
    public function getLatitude(): float
    {
        return $this->latitude;
    }

    /**
     * {@inheritdoc}
     */
    public function setLongitude(float $longitude): void
    {
        $this->longitude = $this->normalizeLongitude($longitude);
    }

    /**
     * {@inheritdoc}
     */
    public function getLongitude(): float
    {
        return $this->longitude;
    }

    /**
     * Creates a valid and acceptable geographic coordinates.
     *
     * @param string $coordinates
     *
     * @throws \InvalidArgumentException
     */
    public function setFromString($coordinates)
    {
        if (! is_string($coordinates)) {
            throw new \InvalidArgumentException('The given coordinates should be a string !');
        }

        $inDecimalDegree = $this->toDecimalDegrees($coordinates);
        $this->setLatitude($inDecimalDegree[0]);
        $this->setLongitude($inDecimalDegree[1]);
    }

    /**
     * Converts a valid and acceptable geographic coordinates to decimal degrees coordinate.
     *
     * @param string $coordinates a valid and acceptable geographic coordinates
     *
     * @return array an array of coordinate in decimal degree
     *
     * @throws \InvalidArgumentException
     *
     * @see http://en.wikipedia.org/wiki/Geographic_coordinate_conversion
     */
    private function toDecimalDegrees($coordinates)
    {
        // 40.446195, -79.948862
        if (preg_match('/(\-?[0-9]{1,2}\.?\d*)[, ] ?(\-?[0-9]{1,3}\.?\d*)$/', $coordinates, $match)) {
            return [$match[1], $match[2]];
        }

        // 40° 26.7717, -79° 56.93172
        if (preg_match('/(\-?[0-9]{1,2})\D+([0-9]{1,2}\.?\d*)[, ] ?(\-?[0-9]{1,3})\D+([0-9]{1,2}\.?\d*)$/i',
            $coordinates, $match)) {
            return [
                $match[1] + $match[2] / 60,
                $match[3] < 0
                    ? $match[3] - $match[4] / 60
                    : $match[3] + $match[4] / 60,
            ];
        }

        // 40.446195N 79.948862W
        if (preg_match('/([0-9]{1,2}\.?\d*)\D*([ns]{1})[, ] ?([0-9]{1,3}\.?\d*)\D*([we]{1})$/i', $coordinates, $match)) {
            return [
                'N' === strtoupper($match[2]) ? $match[1] : -$match[1],
                'E' === strtoupper($match[4]) ? $match[3] : -$match[3],
            ];
        }

        // 40°26.7717S 79°56.93172E
        // 25°59.86′N,21°09.81′W
        if (preg_match('/([0-9]{1,2})\D+([0-9]{1,2}\.?\d*)\D*([ns]{1})[, ] ?([0-9]{1,3})\D+([0-9]{1,2}\.?\d*)\D*([we]{1})$/i',
            $coordinates, $match)) {
            $latitude = $match[1] + $match[2] / 60;
            $longitude = $match[4] + $match[5] / 60;

            return [
                'N' === strtoupper($match[3]) ? $latitude : -$latitude,
                'E' === strtoupper($match[6]) ? $longitude : -$longitude,
            ];
        }

        // 40:26:46N, 079:56:55W
        // 40:26:46.302N 079:56:55.903W
        // 40°26′47″N 079°58′36″W
        // 40d 26′ 47″ N 079d 58′ 36″ W
        if (preg_match('/([0-9]{1,2})\D+([0-9]{1,2})\D+([0-9]{1,2}\.?\d*)\D*([ns]{1})[, ] ?([0-9]{1,3})\D+([0-9]{1,2})\D+([0-9]{1,2}\.?\d*)\D*([we]{1})$/i',
            $coordinates, $match)) {
            $latitude = $match[1] + ($match[2] * 60 + $match[3]) / 3600;
            $longitude = $match[5] + ($match[6] * 60 + $match[7]) / 3600;

            return [
                'N' === strtoupper($match[4]) ? $latitude : -$latitude,
                'E' === strtoupper($match[8]) ? $longitude : -$longitude,
            ];
        }

        throw new \InvalidArgumentException(
            'It should be a valid and acceptable ways to write geographic coordinates !'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [$this->longitude, $this->latitude];
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'lat' => $this->latitude,
            'lon' => $this->longitude,
        ];
    }
}

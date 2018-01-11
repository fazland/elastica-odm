<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Geotools\Geohash;

use Fazland\ODM\Elastica\Geotools\Coordinate\Coordinate;
use Fazland\ODM\Elastica\Geotools\Coordinate\CoordinateInterface;

class Geohash
{
    /**
     * The minimum length of the geo hash.
     *
     * @var int
     */
    const MIN_LENGTH = 1;

    /**
     * The maximum length of the geo hash.
     *
     * @var int
     */
    const MAX_LENGTH = 12;

    /**
     * The geo hash.
     *
     * @var string
     */
    protected $geohash;

    /**
     * The interval of latitudes in degrees.
     *
     * @var array
     */
    protected $latitudeInterval = [-90.0, 90.0];

    /**
     * The interval of longitudes in degrees.
     *
     * @var array
     */
    protected $longitudeInterval = [-180.0, 180.0];

    /**
     * The interval of bits.
     *
     * @var array
     */
    protected $bits = [16, 8, 4, 2, 1];

    /**
     * The array of chars in base 32.
     *
     * @var array
     */
    protected $base32Chars = [
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'b', 'c', 'd', 'e', 'f', 'g',
        'h', 'j', 'k', 'm', 'n', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
    ];

    /**
     * Geohash constructor.
     * You can pass a coordinate instance or a string hash.
     *
     * @param string|CoordinateInterface $hashOrCoordinates
     * @param int                        $length
     */
    public function __construct($hashOrCoordinates, int $length = self::MAX_LENGTH)
    {
        if ($hashOrCoordinates instanceof CoordinateInterface) {
            $this->encode($hashOrCoordinates, $length);
        } else {
            $this->decode($hashOrCoordinates);
        }
    }

    /**
     * Returns the geo hash.
     *
     * @return string
     */
    public function getGeohash(): string
    {
        return $this->geohash;
    }

    /**
     * Returns the decoded coordinate (The center of the bounding box).
     *
     * @return CoordinateInterface
     */
    public function getCoordinate(): CoordinateInterface
    {
        return new Coordinate([
            ($this->latitudeInterval[0] + $this->latitudeInterval[1]) / 2,
            ($this->longitudeInterval[0] + $this->longitudeInterval[1]) / 2,
        ]);
    }

    /**
     * Returns the bounding box which is an array of coordinates (SouthWest & NorthEast).
     *
     * @return CoordinateInterface[]
     */
    public function getBoundingBox()
    {
        return [
            new Coordinate([
                $this->latitudeInterval[0],
                $this->longitudeInterval[0],
            ]),
            new Coordinate([
                $this->latitudeInterval[1],
                $this->longitudeInterval[1],
            ]),
        ];
    }

    /**
     * @see http://en.wikipedia.org/wiki/Geohash
     * @see http://geohash.org/
     */
    private function encode(CoordinateInterface $coordinate, $length = self::MAX_LENGTH): void
    {
        if ((int) $length < self::MIN_LENGTH || (int) $length > self::MAX_LENGTH) {
            throw new \InvalidArgumentException('The length should be between 1 and 12.');
        }

        $latitudeInterval = $this->latitudeInterval;
        $longitudeInterval = $this->longitudeInterval;
        $isEven = true;
        $bit = 0;
        $charIndex = 0;

        $this->geohash = '';
        while (strlen($this->geohash) < $length) {
            if ($isEven) {
                $middle = ($longitudeInterval[0] + $longitudeInterval[1]) / 2;
                if ($coordinate->getLongitude() > $middle) {
                    $charIndex |= $this->bits[$bit];
                    $longitudeInterval[0] = $middle;
                } else {
                    $longitudeInterval[1] = $middle;
                }
            } else {
                $middle = ($latitudeInterval[0] + $latitudeInterval[1]) / 2;
                if ($coordinate->getLatitude() > $middle) {
                    $charIndex |= $this->bits[$bit];
                    $latitudeInterval[0] = $middle;
                } else {
                    $latitudeInterval[1] = $middle;
                }
            }

            if ($bit < 4) {
                ++$bit;
            } else {
                $this->geohash = $this->geohash.$this->base32Chars[$charIndex];
                $bit = 0;
                $charIndex = 0;
            }

            $isEven = $isEven ? false : true;
        }

        $this->latitudeInterval = $latitudeInterval;
        $this->longitudeInterval = $longitudeInterval;
    }

    private function decode($geohash): void
    {
        if (! is_string($geohash)) {
            throw new \InvalidArgumentException('The geo hash should be a string.');
        }

        if (strlen($geohash) < self::MIN_LENGTH || strlen($geohash) > self::MAX_LENGTH) {
            throw new \InvalidArgumentException('The length of the geo hash should be between 1 and 12.');
        }

        $base32DecodeMap = [];
        $base32CharsTotal = count($this->base32Chars);
        for ($i = 0; $i < $base32CharsTotal; ++$i) {
            $base32DecodeMap[$this->base32Chars[$i]] = $i;
        }

        $latitudeInterval = $this->latitudeInterval;
        $longitudeInterval = $this->longitudeInterval;
        $isEven = true;

        $geohashLength = strlen($geohash);
        for ($i = 0; $i < $geohashLength; ++$i) {
            if (! isset($base32DecodeMap[$geohash[$i]])) {
                throw new \RuntimeException('This geo hash is invalid.');
            }

            $currentChar = $base32DecodeMap[$geohash[$i]];

            $bitsTotal = count($this->bits);
            for ($j = 0; $j < $bitsTotal; ++$j) {
                $mask = $this->bits[$j];

                if ($isEven) {
                    if (0 !== ($currentChar & $mask)) {
                        $longitudeInterval[0] = ($longitudeInterval[0] + $longitudeInterval[1]) / 2;
                    } else {
                        $longitudeInterval[1] = ($longitudeInterval[0] + $longitudeInterval[1]) / 2;
                    }
                } else {
                    if (0 !== ($currentChar & $mask)) {
                        $latitudeInterval[0] = ($latitudeInterval[0] + $latitudeInterval[1]) / 2;
                    } else {
                        $latitudeInterval[1] = ($latitudeInterval[0] + $latitudeInterval[1]) / 2;
                    }
                }

                $isEven = $isEven ? false : true;
            }
        }

        $this->latitudeInterval = $latitudeInterval;
        $this->longitudeInterval = $longitudeInterval;
    }
}

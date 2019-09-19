<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Type;

use Fazland\ODM\Elastica\Exception\ConversionFailedException;

abstract class AbstractDateTimeType extends AbstractType
{
    const NAME = 'datetime';

    /**
     * {@inheritdoc}
     */
    public function toPHP($value, array $options = [])
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            $value = $value->format(\DateTime::ISO8601);
        }

        $class = $this->getClass();

        return new $class($value);
    }

    /**
     * {@inheritdoc}
     */
    public function toDatabase($value, array $options = [])
    {
        if (empty($value)) {
            return null;
        }

        $class = $this->getClass();
        if (! $value instanceof $class) {
            throw new ConversionFailedException($value, $class);
        }

        return $value->format($options['format'] ?? \DateTime::ISO8601);
    }

    /**
     * {@inheritdoc}
     */
    public function getMappingDeclaration(array $options = []): array
    {
        return [
            'type' => 'date',
            'format' => $this->toJoda($options['format'] ?? \DateTime::ISO8601),
        ];
    }

    /**
     * Gets the target datetime class.
     *
     * @return string
     */
    abstract protected function getClass(): string;

    private function toJoda(string $format)
    {
        if ('U' === $format) {
            return 'epoch_second';
        }

        return \preg_replace_callback('/(\\\\[a-z0-9]|.)/i', function ($match): string {
            $token = $match[1];
            switch ($token) {
                case 'd':       // Day of the month, 2 digits with leading zeros
                    return 'dd';

                case 'D':       // A textual representation of a day, three letters
                    return 'EEE';

                case 'j':       // Day of the month without leading zeros
                    return 'd';

                case 'l':       // A full textual representation of the day of the week
                    return 'EEEE';

                case 'N':
                case 'w':       // Numeric representation of the day of the week
                    return 'e';

                case 'W':       // ISO-8601 week number of year, weeks starting on Monday (2 digits)
                    return 'ww';

                case 'F':       // A full textual representation of a month, such as January or March
                    return 'MMMM';

                case 'm':       // Numeric representation of a month, with leading zeros
                    return 'MM';

                case 'M':       // A short textual representation of a month, three letters
                    return 'MMM';

                case 'n':       // Numeric representation of a month, without leading zeros
                    return 'M';

                case 'o':       // ISO-8601 week-numbering year.
                    return 'x';

                case 'Y':       // A full numeric representation of a year, 4 digits
                    return 'YYYY';

                case 'y':       // A two digit representation of a year
                    return 'YY';

                case 'A':       // Uppercase Ante meridiem and Post meridiem
                    return 'a';

                case 'g':       // 12-hour format of an hour without leading zeros
                    return 'h';

                case 'G':       // 24-hour format of an hour without leading zeros
                    return 'H';

                case 'h':       // 12-hour format of an hour with leading zeros
                    return 'hh';

                case 'H':       // 24-hour format of an hour with leading zeros
                    return 'HH';

                case 'i':       // Minutes with leading zeros
                    return 'mm';

                case 's':       // Seconds, with leading zeros
                    return 'ss';

                case 'u':       // Microseconds
                    return 'SSSSSS';

                case 'v':       // Milliseconds
                    return 'SSS';

                case 'e':       // Timezone identifier
                    return 'ZZZ';

                case 'O':       // Difference to Greenwich time (GMT) in hours
                    return 'Z';

                case 'P':       // Difference to Greenwich time (GMT) with colon between hours and minutes
                    return 'ZZ';

                case 'c':       // ISO 8601 date
                    return 'YYYY-MM-dd\'T\'HH:mm:ssZ';

                case 'S':       // English ordinal suffix for the day of the month, 2 characters
                case 'z':       // The day of the year (starting from 0)
                case 't':       // Number of days in the given month
                case 'L':       // Whether it's a leap year
                case 'a':       // Lowercase Ante meridiem and Post meridiem
                case 'B':       // Swatch Internet time
                case 'I':       // Whether or not the date is in daylight saving time
                case 'T':       // Timezone abbreviation
                case 'Z':       // Timezone offset in seconds.
                case 'U':       // UNIX timestamp.
                case 'r':       // RFC 2822 formatted date
                    throw new \InvalidArgumentException('Cannot convert token "'.$token.'" for date format');
                default:
                    if ('\\' === $token[0]) {
                        $token = \substr($token, 1);
                    }

                    if (\preg_match('/[a-z0-9]/i', $token)) {
                        return "'$token'";
                    }

                    return $token;
            }
        }, $format);
    }
}

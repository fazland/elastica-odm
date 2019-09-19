<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Type;

use Fazland\ODM\Elastica\Exception\ConversionFailedException;

final class IntegerType extends AbstractType
{
    const NAME = 'integer';

    /**
     * {@inheritdoc}
     */
    public function toPHP($value, array $options = []): ?int
    {
        return $this->doConversion($value);
    }

    /**
     * {@inheritdoc}
     */
    public function toDatabase($value, array $options = []): ?int
    {
        return $this->doConversion($value);
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
        $length = $options['length'] ?? 4;
        switch ($length) {
            case 1:
                $type = 'byte';
                break;

            case 2:
                $type = 'short';
                break;

            case 4:
                $type = 'integer';
                break;

            case 8:
                $type = 'long';
                break;

            default:
                throw new \InvalidArgumentException('Invalid length '.$length.' for integer');
        }

        return ['type' => $type];
    }

    private function doConversion($value): ?int
    {
        if (null === $value) {
            return null;
        }

        if (! \is_numeric($value)) {
            throw new ConversionFailedException($value, self::NAME);
        }

        return (int) $value;
    }
}

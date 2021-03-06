<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Type;

use Fazland\ODM\Elastica\Exception\ConversionFailedException;

final class BooleanType extends AbstractType
{
    public const NAME = 'boolean';

    /**
     * {@inheritdoc}
     */
    public function toPHP($value, array $options = []): ?bool
    {
        return $this->doConversion($value);
    }

    /**
     * {@inheritdoc}
     */
    public function toDatabase($value, array $options = []): ?bool
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
        return ['type' => 'boolean'];
    }

    private function doConversion($value): ?bool
    {
        if (null === $value) {
            return null;
        }

        if (! \is_bool($value)) {
            throw new ConversionFailedException($value, 'bool');
        }

        return $value;
    }
}

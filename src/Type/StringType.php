<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Type;

use Fazland\ODM\Elastica\Exception\ConversionFailedException;

final class StringType extends AbstractType
{
    public const NAME = 'string';

    /**
     * {@inheritdoc}
     */
    public function toPHP($value, array $options = []): ?string
    {
        if (null === $value) {
            return null;
        }

        return (string) $value;
    }

    /**
     * {@inheritdoc}
     */
    public function toDatabase($value, array $options = []): ?string
    {
        if (null === $value) {
            return null;
        }

        if (! \is_string($value)) {
            throw new ConversionFailedException($value, 'string');
        }

        return $value;
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
        $type = ($options['analyzed'] ?? true) ? 'text' : 'keyword';

        return \array_filter([
            'type' => $type,
            'analyzer' => $options['analyzer'] ?? null,
            'search_analyzer' => $options['search_analyzer'] ?? null,
            'term_vector' => $options['term_vector'] ?? null,
        ]);
    }
}

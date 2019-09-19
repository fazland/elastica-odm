<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Type;

use Fazland\ODM\Elastica\Completion;
use Fazland\ODM\Elastica\Exception\ConversionFailedException;

final class CompletionType extends AbstractType
{
    const NAME = 'completion';

    /**
     * {@inheritdoc}
     */
    public function toPHP($value, array $options = []): ?Completion
    {
        if (null === $value) {
            return null;
        }

        if (\is_array($value) && isset($value['input'])) {
            $completion = new Completion();
            $completion->input = $value['input'];
            $completion->weight = $value['weight'] ?? null;

            return $completion;
        }

        if (! \is_string($value)) {
            throw new ConversionFailedException($value, Completion::class);
        }

        $completion = new Completion();
        $completion->input = $value;

        return $completion;
    }

    /**
     * {@inheritdoc}
     */
    public function toDatabase($value, array $options = []): ?array
    {
        if (null === $value) {
            return null;
        }

        if (! $value instanceof Completion) {
            throw new ConversionFailedException($value, Completion::class);
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
        return \array_filter([
            'type' => 'completion',
            'analyzer' => $options['analyzer'] ?? null,
            'search_analyzer' => $options['search_analyzer'] ?? null,
            'preserve_separators' => $options['preserve_separators'] ?? null,
            'preserve_position_increments' => $options['preserve_position_increments'] ?? null,
        ], function ($value) {
            return null !== $value;
        });
    }
}

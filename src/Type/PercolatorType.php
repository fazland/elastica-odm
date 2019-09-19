<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Type;

use Elastica\Query;
use Fazland\ODM\Elastica\Exception\ConversionFailedException;

final class PercolatorType extends AbstractType
{
    const NAME = 'percolator';

    /**
     * {@inheritdoc}
     */
    public function toPHP($value, array $options = []): ?Query
    {
        if (null === $value) {
            return null;
        }

        if (! \is_array($value) && ! $value instanceof Query) {
            throw new ConversionFailedException($value, 'array');
        }

        return Query::create(['query' => $value]);
    }

    /**
     * {@inheritdoc}
     */
    public function toDatabase($value, array $options = []): ?array
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof Query) {
            $value = $value->getQuery();
        }

        if ($value instanceof Query\AbstractQuery) {
            return $value->toArray();
        } elseif (\is_array($value)) {
            return $value;
        }

        throw new ConversionFailedException($value, [Query::class, Query\AbstractQuery::class, 'array']);
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
        return ['type' => 'percolator'];
    }
}

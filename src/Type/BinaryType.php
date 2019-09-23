<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Type;

final class BinaryType extends AbstractType
{
    public const NAME = 'binary';

    /**
     * {@inheritdoc}
     */
    public function toPHP($value, array $options = []): ?string
    {
        if (empty($value)) {
            return null;
        }

        return \base64_decode($value, true);
    }

    /**
     * {@inheritdoc}
     */
    public function toDatabase($value, array $options = []): ?string
    {
        if (empty($value)) {
            return null;
        }

        return \base64_encode($value);
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
        return ['type' => 'binary'];
    }
}

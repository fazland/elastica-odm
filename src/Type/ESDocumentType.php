<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Type;

use Fazland\ODM\Elastica\DocumentManagerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * TODO: Please remove this and handle associations in uow.
 */
final class ESDocumentType extends AbstractType implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    const NAME = 'es_document';

    /**
     * {@inheritdoc}
     */
    public function toPHP($value, array $options = [])
    {
        if (empty($value)) {
            return null;
        }

        $om = $this->container->get(DocumentManagerInterface::class);

        return $om->find($options['class'], $value);
    }

    /**
     * {@inheritdoc}
     */
    public function toDatabase($value, array $options = [])
    {
        // @todo
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::NAME;
    }
}

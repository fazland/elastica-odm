<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class DocumentId
{
    /**
     * Id generator strategy.
     *
     * @var string
     * @Required()
     * @Enum({"auto", "none"})
     */
    public $strategy = 'auto';
}

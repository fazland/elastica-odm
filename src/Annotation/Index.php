<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
final class Index
{
    /**
     * The filters of this index.
     *
     * @var \Fazland\ODM\Elastica\Annotation\Filter[]
     */
    public $filters;

    /**
     * The analyzers of this index.
     *
     * @var \Fazland\ODM\Elastica\Annotation\Analyzer[]
     */
    public $analyzers;

    /**
     * The tokenizers of this index.
     *
     * @var \Fazland\ODM\Elastica\Annotation\Tokenizer[]
     */
    public $tokenizers;
}

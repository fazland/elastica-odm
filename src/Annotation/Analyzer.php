<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"ANNOTATION"})
 */
final class Analyzer
{
    /**
     * The name of the analyzer.
     *
     * @var string
     *
     * @Required()
     */
    public $name;

    /**
     * The tokenizer of the analyzer.
     *
     * @var string
     *
     * @Required()
     */
    public $tokenizer;

    /**
     * Array of char filters name.
     *
     * @var string[]
     */
    public $charFilters;

    /**
     * Array of filters.
     *
     * @var string[]
     */
    public $filters;
}

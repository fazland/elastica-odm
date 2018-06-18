<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"ANNOTATION"})
 */
final class Filter
{
    /**
     * The name of this filter.
     *
     * @var string
     *
     * @Required()
     */
    public $name;

    /**
     * The type of this filter.
     *
     * @var string
     *
     * @Required()
     */
    public $type;

    /**
     * Type-specific options.
     *
     * @var array
     */
    public $options;
}

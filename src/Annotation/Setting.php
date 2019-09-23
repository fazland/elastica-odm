<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
final class Setting
{
    /**
     * Id generator strategy.
     *
     * @var string
     * @Enum({"auto", "static", "dynamic"})
     */
    public $type = 'auto';

    /**
     * The setting key.
     *
     * @var string
     * @Required()
     */
    public $key;

    /**
     * The setting value.
     *
     * @Required()
     *
     * @var mixed
     */
    public $value;
}

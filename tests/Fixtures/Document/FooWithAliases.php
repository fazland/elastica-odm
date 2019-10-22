<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Fixtures\Document;

use Fazland\ODM\Elastica\Annotation\Document;
use Fazland\ODM\Elastica\Annotation\DocumentId;
use Fazland\ODM\Elastica\Annotation\Field;

/**
 * @Document(type="foo_with_aliases_index/foo_type")
 */
class FooWithAliases
{
    /**
     * @var string
     *
     * @DocumentId(strategy="none")
     */
    public $id;

    /**
     * @var string
     *
     * @Field(type="string")
     */
    public $stringField;
}

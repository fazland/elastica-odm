<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Fixtures\Document;

use Fazland\ODM\Elastica\Annotation\Analyzer;
use Fazland\ODM\Elastica\Annotation\Document;
use Fazland\ODM\Elastica\Annotation\DocumentId;
use Fazland\ODM\Elastica\Annotation\Field;
use Fazland\ODM\Elastica\Annotation\Filter;
use Fazland\ODM\Elastica\Annotation\Index;
use Fazland\ODM\Elastica\Annotation\Tokenizer;

/**
 * @Document(type="foo_lazy_index/foo_type")
 * @Index(analyzers={
 *     @Analyzer(name="foo_analyzer", tokenizer="foo_tokenizer", charFilters={"html_strip"}, filters={"lowercase", "english_stop", "english_stemmer"})
 * }, tokenizers={
 *     @Tokenizer(name="foo_tokenizer", type="edge_ngram", options={"min_gram": 3, "max_gram": 15, "token_chars": {"letter", "digit"}})
 * }, filters={
 *     @Filter(name="english_stop", type="stop", options={"stopwords": "_english_"}),
 *     @Filter(name="english_stemmer", type="stemmer", options={"language": "english"}),
 * })
 */
class FooWithLazyField
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

    /**
     * @var string
     *
     * @Field(type="string", lazy=true)
     */
    public $lazyField;
}

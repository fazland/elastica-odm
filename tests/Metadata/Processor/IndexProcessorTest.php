<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Tests\Metadata\Processor;

use Elastica\Type\Mapping;
use Fazland\ODM\Elastica\Annotation\Analyzer;
use Fazland\ODM\Elastica\Annotation\Filter;
use Fazland\ODM\Elastica\Annotation\Index;
use Fazland\ODM\Elastica\Annotation\Tokenizer;
use Fazland\ODM\Elastica\Metadata\DocumentMetadata;
use Fazland\ODM\Elastica\Metadata\Processor\IndexProcessor;
use Fazland\ODM\Elastica\Tests\Fixtures\Document\Foo;
use Fazland\ODM\Elastica\Tests\Traits\DocumentManagerTestTrait;
use PHPUnit\Framework\TestCase;

class IndexProcessorTest extends TestCase
{
    use DocumentManagerTestTrait;

    /**
     * @var IndexProcessor
     */
    private $processor;

    /**
     * @var DocumentMetadata
     */
    private $documentMetadata;

    protected function setUp(): void
    {
        $this->processor = new IndexProcessor();
        $this->documentMetadata = new DocumentMetadata(new \ReflectionClass(Foo::class));
    }

    public function testAnalyzersAreProcessedCorrectly(): void
    {
        $index = new Index();

        $analyzer = new Analyzer();
        $analyzer->name = 'foo_name';
        $analyzer->tokenizer = 'foo_tokenizer';

        $index->analyzers = [$analyzer];

        $this->processor->process($this->documentMetadata, $index);

        self::assertEquals([
            'settings' => [
                'analysis' => [
                    'analyzer' => [
                        'foo_name' => [
                            'tokenizer' => 'foo_tokenizer',
                        ],
                    ],
                ],
            ],
        ], $this->documentMetadata->indexParams);
    }

    public function testFiltersAreProcessedCorrectly(): void
    {
        $index = new Index();

        $filter = new Filter();
        $filter->name = 'foo_name';
        $filter->type = 'stop';
        $filter->options = [
            'stopwords' => '_english_',
        ];

        $index->filters = [$filter];

        $this->processor->process($this->documentMetadata, $index);

        self::assertEquals([
            'settings' => [
                'analysis' => [
                    'filter' => [
                        'foo_name' => [
                            'type' => 'stop',
                            'stopwords' => '_english_',
                        ],
                    ],
                ],
            ],
        ], $this->documentMetadata->indexParams);
    }

    public function testTokenizersAreProcessedCorrectly(): void
    {
        $index = new Index();

        $tokenizer = new Tokenizer();
        $tokenizer->name = 'foo_name';
        $tokenizer->type = 'ngram';
        $tokenizer->options = [
            'min_gram' => 3,
        ];

        $index->tokenizers = [$tokenizer];

        $this->processor->process($this->documentMetadata, $index);

        self::assertEquals([
            'settings' => [
                'analysis' => [
                    'tokenizer' => [
                        'foo_name' => [
                            'type' => 'ngram',
                            'min_gram' => 3,
                        ],
                    ],
                ],
            ],
        ], $this->documentMetadata->indexParams);
    }

    /**
     * @group functional
     */
    public function testIndexIsCreatedWithCorrectIndexParams(): void
    {
        $dm = static::createDocumentManager();
        $collection = $dm->getCollection(Foo::class);
        $collection->drop();
        $collection->updateMapping(Mapping::create([
            'stringField' => ['type' => 'text'],
        ]));

        $database = $dm->getDatabase();
        $connection = $database->getConnection();

        $fooIndex = $connection->getIndex('foo_index');
        self::assertArrayHasKey('analysis', $fooIndex->getSettings()->get());
        self::assertEquals([
            'filter' => [
                'english_stemmer' => [
                    'type' => 'stemmer',
                    'language' => 'english',
                ],
                'english_stop' => [
                    'type' => 'stop',
                    'stopwords' => '_english_',
                ],
            ],
            'analyzer' => [
                'foo_analyzer' => [
                    'tokenizer' => 'foo_tokenizer',
                    'char_filter' => [
                        'html_strip',
                    ],
                    'filter' => [
                        'lowercase',
                        'english_stop',
                        'english_stemmer',
                    ],
                ],
            ],
            'tokenizer' => [
                'foo_tokenizer' => [
                    'type' => 'edge_ngram',
                    'min_gram' => 3,
                    'max_gram' => 15,
                    'token_chars' => [
                        'letter',
                        'digit',
                    ],
                ],
            ],
        ], $fooIndex->getSettings()->get('analysis'));
    }
}

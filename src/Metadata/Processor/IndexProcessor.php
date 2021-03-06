<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Metadata\Processor;

use Fazland\ODM\Elastica\Annotation\Index;
use Fazland\ODM\Elastica\Metadata\DocumentMetadata;
use Kcs\Metadata\Loader\Processor\Annotation\Processor;
use Kcs\Metadata\Loader\Processor\ProcessorInterface;
use Kcs\Metadata\MetadataInterface;

/**
 * @Processor(annotation=Index::class)
 */
class IndexProcessor implements ProcessorInterface
{
    /**
     * {@inheritdoc}
     *
     * @param DocumentMetadata $metadata
     * @param Index            $subject
     */
    public function process(MetadataInterface $metadata, $subject): void
    {
        $analysis = [
            'filter' => [],
            'tokenizer' => [],
            'analyzer' => [],
        ];

        foreach ($subject->filters ?? [] as $filter) {
            $setting = ['type' => $filter->type];
            $analysis['filter'][$filter->name] = \array_merge($setting, $filter->options);
        }

        foreach ($subject->tokenizers ?? [] as $tokenizer) {
            $setting = ['type' => $tokenizer->type];
            $analysis['tokenizer'][$tokenizer->name] = \array_merge($setting, $tokenizer->options);
        }

        foreach ($subject->analyzers ?? [] as $analyzer) {
            $analysis['analyzer'][$analyzer->name] = \array_filter([
                'tokenizer' => $analyzer->tokenizer,
                'char_filter' => $analyzer->charFilters,
                'filter' => $analyzer->filters,
            ]);
        }

        $analysis = \array_filter($analysis);

        if (! empty($analysis)) {
            $metadata->staticSettings['analysis'] = $analysis;
        }
    }
}

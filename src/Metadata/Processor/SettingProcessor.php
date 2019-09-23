<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Metadata\Processor;

use Fazland\ODM\Elastica\Annotation\Setting;
use Fazland\ODM\Elastica\Metadata\DocumentMetadata;
use Kcs\Metadata\Loader\Processor\Annotation\Processor;
use Kcs\Metadata\Loader\Processor\ProcessorInterface;
use Kcs\Metadata\MetadataInterface;

/**
 * @Processor(annotation=Setting::class)
 */
class SettingProcessor implements ProcessorInterface
{
    private const DYNAMIC_SETTINGS = [
        'index.number_of_replicas' => true,
        'index.auto_expand_replicas' => true,
        'index.search.idle.after' => true,
        'index.refresh_interval' => true,
        'index.max_result_window' => true,
        'index.max_inner_result_window' => true,
        'index.max_rescore_window' => true,
        'index.max_docvalue_fields_search' => true,
        'index.max_script_fields' => true,
        'index.max_ngram_diff' => true,
        'index.max_shingle_diff' => true,
        'index.blocks.read_only' => true,
        'index.blocks.read_only_allow_delete' => true,
        'index.blocks.read' => true,
        'index.blocks.write' => true,
        'index.blocks.metadata' => true,
        'index.max_refresh_listeners' => true,
        'index.analyze.max_token_count' => true,
        'index.highlight.max_analyzed_offset' => true,
        'index.max_terms_count' => true,
        'index.max_regex_length' => true,
        'index.routing.allocation.enable' => true,
        'index.routing.rebalance.enable' => true,
        'index.gc_deletes' => true,
        'index.default_pipeline' => true,
        'index.unassigned.node_left.delayed_timeout' => true,
        'index.routing.allocation.total_shards_per_node' => true,
        'index.mapping.total_fields.limit' => true,
        'index.mapping.depth.limit' => true,
        'index.mapping.nested_fields.limit' => true,
        'index.mapping.nested_objects.limit' => true,
        'index.mapping.field_name_length.limit' => true,
        'index.merge.scheduler.max_thread_count' => true,
        'index.search.slowlog.level' => true,
        'index.search.slowlog.source' => true,
        'index.search.slowlog.threshold.query.warn' => true,
        'index.search.slowlog.threshold.query.info' => true,
        'index.search.slowlog.threshold.query.debug' => true,
        'index.search.slowlog.threshold.query.trace' => true,
        'index.search.slowlog.threshold.fetch.warn' => true,
        'index.search.slowlog.threshold.fetch.info' => true,
        'index.search.slowlog.threshold.fetch.debug' => true,
        'index.search.slowlog.threshold.fetch.trace' => true,
        'index.translog.sync_interval' => true,
        'index.translog.durability' => true,
        'index.translog.flush_threshold_size' => true,
        'index.translog.retention.size' => true,
        'index.translog.retention.age' => true,
    ];

    /**
     * {@inheritdoc}
     *
     * @param DocumentMetadata $metadata
     * @param Setting          $subject
     */
    public function process(MetadataInterface $metadata, $subject): void
    {
        $settingType = $subject->type;
        if ('auto' === $settingType) {
            if (isset(self::DYNAMIC_SETTINGS[$subject->key]) || \preg_match('/^index\.routing\.allocation\.(include|require|exclude)\..+/', $subject->key)) {
                $settingType = 'dynamic';
            } else {
                $settingType = 'static';
            }
        }

        if ('static' === $settingType) {
            $metadata->staticSettings[ $subject->key ] = $subject->value;
        } else {
            $metadata->dynamicSettings[$subject->key] = $subject->value;
        }
    }
}

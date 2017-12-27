<?php declare(strict_types=1);

namespace Fazland\ODM\Elastica\Search;

final class SearchCacheProfile
{
    /**
     * @var string
     */
    private $cacheKey;

    /**
     * @var int
     */
    private $ttl;

    public function __construct(string $cacheKey, int $ttl = 0)
    {
        $this->cacheKey = $cacheKey;
        $this->ttl = $ttl;
    }

    /**
     * @return string
     */
    public function getCacheKey(): string
    {
        return $this->cacheKey;
    }

    /**
     * @return int
     */
    public function getTtl(): int
    {
        return $this->ttl;
    }
}

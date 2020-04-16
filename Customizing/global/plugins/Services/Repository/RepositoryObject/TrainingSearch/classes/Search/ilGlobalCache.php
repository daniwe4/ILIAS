<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\TrainingSearch\Search;

/**
 * An implementation for a cache over the ILIAS Global Cache.
 */
class ilGlobalCache implements Cache
{
    /**
     * @var \ilGlobalCache
     */
    protected $il_global_cache;

    /**
     * @var int
     */
    protected $ttl_in_seconds;

    const PREFIX = "tms_training_search_";

    public function __construct(\ilGlobalCache $cache, int $ttl_in_seconds)
    {
        $this->il_global_cache = $cache;
        $this->ttl_in_seconds = $ttl_in_seconds;
    }

    /**
     * @return void
     */
    public function set(string $key, array $value)
    {
        return $this->il_global_cache->set(self::PREFIX . $key, $value, $this->ttl_in_seconds);
    }

    /**
     * @return array|null
     */
    public function get(string $key)
    {
        return $this->il_global_cache->get(self::PREFIX . $key);
    }
}

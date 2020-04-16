<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\TrainingSearch\Search;

/**
 * Contract for a cache.
 */
interface Cache
{
    /**
     * @return void
     */
    public function set(string $key, array $value);

    /**
     * @return array|null
     */
    public function get(string $key);
}

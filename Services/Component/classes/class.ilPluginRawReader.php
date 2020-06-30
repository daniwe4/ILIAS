<?php
/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

class ilPluginRawReader
{
    const BASE_PLUGIN_PATH = 'Customizing/global/plugins';
    const SEARCH_PATTERN = 'plugin.php';

    public function getPluginNames() : ?\Iterator
    {
        if (!@is_dir(self::BASE_PLUGIN_PATH)) {
            return null;
        }
        $it = new RecursiveDirectoryIterator(self::BASE_PLUGIN_PATH);
        foreach(new RecursiveIteratorIterator($it) as $file) {
            $path = $file->getPathName();
            if (is_file($path) && basename($path) === self::SEARCH_PATTERN) {
                yield basename(dirname($path));
            }
        }
    }
}
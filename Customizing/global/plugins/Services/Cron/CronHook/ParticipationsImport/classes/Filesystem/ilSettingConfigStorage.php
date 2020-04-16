<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\Filesystem;

class ilSettingConfigStorage implements ConfigStorage
{
    const KEYWORD_PREFIX = 'part_imp_filesystem_';
    const KEYWORD_PATH = self::KEYWORD_PREFIX . 'path';
    const KEYWORD_FILE = self::KEYWORD_PREFIX . 'file';

    public function __construct(\ilSetting $set)
    {
        $this->set = $set;
    }
    public function loadCurrentConfig() : Config
    {
        return new Config(
            (string) $this->set->get(self::KEYWORD_PATH),
            (string) $this->set->get(self::KEYWORD_FILE)
        );
    }
    public function storeConfigAsCurrent(Config $config)
    {
        $this->set->set(self::KEYWORD_PATH, $config->path());
        $this->set->set(self::KEYWORD_FILE, $config->filetitleTemplate());
    }
}

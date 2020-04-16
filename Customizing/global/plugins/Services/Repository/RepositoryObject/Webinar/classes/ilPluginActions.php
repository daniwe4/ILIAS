<?php

namespace CaT\Plugins\Webinar;

class ilPluginActions
{
    const VC_FOLDER = "VC";

    /**
     * @var \ilWebinarPlugin
     */
    protected $plugin;

    public function __construct(\ilWebinarPlugin $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Get all available VC types from VC folder
     *
     * @return string[]
     */
    public function getAvailableVCTypes()
    {
        $ret = array();

        if ($handle = opendir($this->plugin->getDirectory() . "/classes/" . self::VC_FOLDER)) {
            while ($entry = readdir($handle)) {
                if ($entry != "." && $entry != "..") {
                    if (is_dir($this->plugin->getDirectory() . "/classes/" . self::VC_FOLDER . "/" . $entry)) {
                        $ret[] = $entry;
                    }
                }
            }
            closedir($handle);
        }

        return $ret;
    }
}

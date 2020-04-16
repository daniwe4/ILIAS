<?php

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

declare(strict_types=1);

namespace CaT\Plugins\WBDCommunicator\Config\Tgic;

class FileStorage extends \ilFileSystemStorage
{
    const PLUGIN_PATH_PREFIX = "Plugin/WBDCommunicator";

    public function __construct($a_container_id = 0)
    {
        parent::__construct(self::STORAGE_DATA, true, $a_container_id);
    }

    protected function getPathPostfix()
    {
        return 'wbd';
    }

    protected function getPathPrefix()
    {
        return self::PLUGIN_PATH_PREFIX . "/Certificate";
    }

    public function isEmpty()
    {
        $files = $this->readDir();

        if (count($files) > 0) {
            return false;
        }

        return true;
    }

    public function getFilePath()
    {
        $files = $this->readDir();
        return $this->getAbsolutePath() . "/" . $files[0];
    }

    public function uploadFile($file_infos)
    {
        $this->create();
        $path = $this->getAbsolutePath();

        $clean_name = preg_replace("/[^a-zA-Z0-9\_\.\-]/", "", $file_infos["name"]);
        $new_file = $path . "/" . $clean_name;

        if (move_uploaded_file($file_infos["tmp_name"], $new_file)) {
            return $new_file;
        }

        return null;
    }

    /**
     * Read files from absolute path
     *
     * @return string[]
     */
    protected function readDir()
    {
        if (!is_dir($this->getAbsolutePath())) {
            $this->create();
        }

        $fh = opendir($this->getAbsolutePath());
        $files = array();
        while ($file = readdir($fh)) {
            if ($file != "." && $file != ".." && !is_dir($this->getAbsolutePath() . "/" . $file)) {
                $files[] = $file;
            }
        }
        closedir($fh);

        return $files;
    }

    public function fileExists(string $file) : bool
    {
        return is_file($file);
    }
}

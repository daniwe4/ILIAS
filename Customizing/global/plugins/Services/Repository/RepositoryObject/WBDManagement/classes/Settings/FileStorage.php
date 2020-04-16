<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WBDManagement\Settings;

require_once './Services/FileSystem/classes/class.ilFileSystemStorage.php';

class FileStorage extends \ilFileSystemStorage
{
    const PLUGIN_PATH_PREFIX = "Plugin/WBDManagement/Settings";
    const PLUGIN_PATH_POSTFIX = "xwbm";

    final public function __construct(int $container_id = 0)
    {
        parent::__construct(self::STORAGE_DATA, true, $container_id);
    }

    protected function getPathPostfix()
    {
        return self::PLUGIN_PATH_POSTFIX;
    }

    protected function getPathPrefix()
    {
        return self::PLUGIN_PATH_PREFIX;
    }

    public function isEmpty() : bool
    {
        $files = $this->readDir();

        if (count($files) > 0) {
            return false;
        }

        return true;
    }

    public function deleteCurrentFile()
    {
        $files = $this->readDir();
        $this->deleteFile($this->getAbsolutePath() . "/" . $files[0]);
    }

    public function getFilePath() : string
    {
        $files = $this->readDir();
        return $this->getAbsolutePath() . "/" . $files[0];
    }

    public function uploadFile(array $file_infos) : bool
    {
        $this->create();
        $path = $this->getAbsolutePath();

        $clean_name = preg_replace("/[^a-zA-Z0-9\_\.\-]/", "", $file_infos["name"]);
        $new_file = $path . "/" . $clean_name;

        if (move_uploaded_file($file_infos["tmp_name"], $new_file)) {
            chmod($new_file, 0770);
            return true;
        }

        return false;
    }

    public function createExportFolder()
    {
        $this->create();
    }

    protected function readDir() : array
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

    public function copyFileFrom(string $source_file_path) : string
    {
        $file_name = substr(
            $source_file_path,
            strrpos(
                $source_file_path,
                "/"
            ) + 1,
            strlen($source_file_path)
        );

        $this->create();
        $file_path = $this->getAbsolutePath() . "/" . $file_name;

        $this->copyFile($source_file_path, $file_path);

        return $file_path;
    }
}

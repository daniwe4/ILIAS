<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\CourseMember\SignatureList;

use CaT\Plugins\CourseMember\FileStorage;

/**
 * Class for file storage of siglist-image
 */
class ilFileStorage extends \ilFileSystemStorage
{
    const PLUGIN_PATH_PREFIX = "Plugin/CourseMember";

    /**
     * @param int | string 	$a_container_id
     */
    public function __construct($a_container_id = 0)
    {
        parent::__construct(self::STORAGE_WEB, true, $a_container_id);
    }

    /**
     * @inheritdoc
     */
    protected function getPathPostfix()
    {
        return 'xcmb';
    }

    /**
     * @inheritdoc
     */
    protected function getPathPrefix()
    {
        return self::PLUGIN_PATH_PREFIX;
    }

    /**
     * Check whether the file directory is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        $files = $this->readDir();

        if (count($files) > 0) {
            return false;
        }

        return true;
    }

    /**
     * Delete current uploaded file
     *
     * @return null
     */
    public function deleteCurrentFile()
    {
        $files = $this->readDir();
        $this->deleteFile($this->getAbsolutePath() . "/" . $files[0]);
    }

    public function uploadFile($file_infos)
    {
        $path = $this->getAbsolutePath();
        if (!is_dir($path)) {
            $this->create();
        }

        $clean_name = preg_replace("/[^a-zA-Z0-9\_\.\-]/", "", $file_infos["name"]);
        $new_file = $path . "/" . $clean_name;

        if (move_uploaded_file($file_infos["tmp_name"], $new_file)) {
            chmod($new_file, 0770);
            return true;
        }

        return false;
    }

    /**
     * Get full path inclusive filename
     *
     * @return string | null
     */
    public function getFilePath()
    {
        $files = $this->readDir();
        $path = $this->getAbsolutePath() . "/" . $files[0];

        if (!is_file($path)) {
            return null;
        }
        return $path;
    }

    /**
     * Create folders
     *
     * @return null
     */
    public function createExportFolder()
    {
        $this->create();
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
}

<?php

namespace CaT\Plugins\Webinar\VC\CSN;

use CaT\Plugins\Webinar\VC\FileStorage;

/**
 * File storage for CSN uploaded files
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class FileStorageImpl extends FileStorage
{
    /**
     * @inheritdoc
     */
    protected function getPathPostfix()
    {
        return 'xwbr';
    }

    /**
     * @inheritdoc
     */
    protected function getPathPrefix()
    {
        return self::PLUGIN_PATH_PREFIX . "/CSN";
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function deleteCurrentFile()
    {
        $files = $this->readDir();
        $this->deleteFile($this->getAbsolutePath() . "/" . $files[0]);
    }

    /**
     * @inheritdoc
     */
    public function getFilePath()
    {
        $files = $this->readDir();
        return $this->getAbsolutePath() . "/" . $files[0];
    }

    /**
     * @inheritdoc
     */
    public function uploadFile($file_infos)
    {
        $path = $this->getAbsolutePath();

        $clean_name = preg_replace("/[^a-zA-Z0-9\_\.\-]/", "", $file_infos["name"]);
        $new_file = $path . "/" . $clean_name;

        if (move_uploaded_file($file_infos["tmp_name"], $new_file)) {
            chmod($new_file, 0770);
            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
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

<?php

namespace CaT\Plugins\Webinar\VC;

include_once('Services/FileSystem/classes/class.ilFileSystemStorage.php');

/**
 * Abstract class for file storage
 * Each VC has to extend because of different path prefix.
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
abstract class FileStorage extends \ilFileSystemStorage
{
    const PLUGIN_PATH_PREFIX = "Plugin/Webinar";

    /**
     * @param int | string 	$a_container_id
     */
    final public function __construct($a_container_id = 0)
    {
        parent::__construct(self::STORAGE_DATA, true, $a_container_id);
    }

    /**
     * @inheritdoc
     */
    protected function getPathPostfix()
    {
        return 'xwbr';
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

    /**
     * Get full path inclusive filename
     *
     * @return string
     */
    public function getFilePath()
    {
        $files = $this->readDir();
        return $this->getAbsolutePath() . "/" . $files[0];
    }

    /**
     * Move file from tmp folder to final destination
     *
     * @param array 	$file_infos
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

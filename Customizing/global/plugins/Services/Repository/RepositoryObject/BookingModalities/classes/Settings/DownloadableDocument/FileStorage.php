<?php
namespace CaT\Plugins\BookingModalities\Settings\DownloadableDocument;

include_once('Services/FileSystem/classes/class.ilFileSystemStorage.php');

/**
 * Class for file storage
 *
 * @author Nils Haagen 	<nils.haagen@concepts-and-training.de>
 */
class FileStorage extends \ilFileSystemStorage
{
    const PLUGIN_PATH_PREFIX = "Plugin/BookingModalities";

    public function __construct()
    {
        parent::__construct(self::STORAGE_WEB, false, 0);
    }

    /**
     * @inheritdoc
     */
    protected function getPathPrefix()
    {
        return self::PLUGIN_PATH_PREFIX;
    }

    /**
     * @inheritdoc
     */
    protected function getPathPostfix()
    {
        return 'xbkm';
    }

    /**
     * Get the file's path
     * @param string $filename
     * @return string | null
     */
    public function getFilePath($filename)
    {
        if (!$filename) {
            return null;
        }
        return $this->getAbsolutePath() . '/' . $filename;
    }

    /**
     * Does the file exist?
     * @param string $filename
     * @return boolean
     */
    public function fileExists($filename)
    {
        $fpath = $this->getFilePath($filename);
        if (!$fpath) {
            return false;
        }
        return file_exists($fpath);
    }

    /**
     * Delete a file.
     * @param string $filename
     * @return boolean
     */
    public function deleteSingleFile($filename)
    {
        $fpath = $this->getFilePath($filename);
        if (!$fpath) {
            return false;
        }
        return $this->deleteFile($fpath);
    }

    /**
     * @inheritdoc
     */
    public function uploadFile($file_infos)
    {
        $new_file = $this->getFilePath($file_infos['name']);
        if (!is_dir($this->getAbsolutePath())) {
            $this->create();
        }

        if (file_exists($new_file)) {
            $this->deleteFile($new_file);
        }

        if (move_uploaded_file($file_infos["tmp_name"], $new_file)) {
            chmod($new_file, 0770);
            return true;
        }
        return false;
    }

    /**
     * Read files from absolute path
     *
     * @return string[]
     */
    public function readDir()
    {
        if (!is_dir($this->getAbsolutePath())) {
            $this->create();
        }

        $fh = opendir($this->getAbsolutePath());
        $files = array();
        while ($file = readdir($fh)) {
            if ($file != "." && $file != ".."
                && !is_dir($this->getAbsolutePath() . "/" . $file)) {
                $files[] = $file;
            }
        }
        closedir($fh);
        return $files;
    }
}

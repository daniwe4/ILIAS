<?php

// GOA special hack for #3410

namespace CaT\Plugins\EduBiography\FileStorage;

class ilCertificateStorage extends \ilFileSystemStorage
{
    const PLUGIN_PATH_PREFIX = "Plugin/EduBiography/Certificates";

    /**
     * @var integer | null
     */
    protected $user_id;

    /**
     * @param int | string 	$a_container_id
     */
    public function __construct($a_container_id = 0)
    {
        parent::__construct(self::STORAGE_DATA, true, $a_container_id);
        $this->user_id = null;
    }

    public function withUserId(int $user_id) : ilCertificateStorage
    {
        $clone = clone $this;
        $clone->user_id = $user_id;
        return $clone;
    }

    public function withCourseId(int $course_id) : ilCertificateStorage
    {
        $clone = clone $this;
        $clone->container_id = $course_id;
        $clone->init();
        return $clone;
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
     * Get absolute path of storage directory
     *
     * @access public
     *
     */
    public function getAbsolutePath()
    {
        if (is_null($this->user_id)) {
            throw new \LogicException("User id is not set");
        }
        return $this->path . "/usr_" . $this->user_id;
    }

    /**
     * Create directory
     *
     * @access public
     *
     */
    public function create()
    {
        if (!file_exists($this->getAbsolutePath())) {
            require_once("Services/Utilities/classes/class.ilUtil.php");
            \ilUtil::makeDirParents($this->getAbsolutePath());
        }
        return true;
    }

    /**
     * Delete current uploaded file
     *
     * @return null
     */
    public function deleteCurrentCertificate()
    {
        $files = $this->readDir();
        $this->deleteFile($this->getAbsolutePath() . "/" . $files[0]);
    }

    /**
     * Get full path inclusive filename
     *
     * @return string
     */
    public function getPathOfCurrentCertificate($filename)
    {
        $files = $this->readDir();
        $files = array_filter($files, function ($f) use ($filename) {
            return $f == $filename;
        });

        if (count($files) == 0) {
            return null;
        }

        return $this->getAbsolutePath() . "/" . array_shift($files);
    }

    public function existsFile(string $filename) : bool
    {
        $files = $this->readDir();

        foreach ($files as $file) {
            if ($file == $filename) {
                return true;
            }
        }
        return false;
    }

    /**
     * Save content to new file
     *
     * @param string 	$file_content
     * @param string 	$filename
     *
     * @return string
     */
    public function saveCertificate($file_content, $filename)
    {
        $this->createFolder();
        $path = $this->getAbsolutePath();

        $clean_name = preg_replace("/[^a-zA-Z0-9\_\.\-]/", "", $filename);
        $new_file = $path . "/" . $clean_name;

        $fh = fopen($new_file, "w");
        fwrite($fh, $file_content);
        fclose($fh);

        return $new_file;
    }

    /**
     * Create folders
     *
     * @return null
     */
    public function createFolder()
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

// GOA special hack for #3410

<?php

namespace CaT\Plugins\EduBiography\Config\OverviewCertificate\ParticipationDocument;

use \ILIAS\Filesystem\Filesystem;

class ilFileStorage
{
    const IMAGE_PATH = "Plugin/EduBiography/ParticipationDocument";
    const TMP_NAME = "tmp_name";
    const FILE_NAME = "name";

    /**
     * @var Filesystem
     */
    protected $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function deleteFiles()
    {
        if ($this->filesystem->hasDir(self::IMAGE_PATH)) {
            $this->filesystem->deleteDir(self::IMAGE_PATH);
        }
        $this->filesystem->createDir(self::IMAGE_PATH);
    }

    public function upload(array $file_infos)
    {
        $target =
            CLIENT_WEB_DIR
            . "/"
            . self::IMAGE_PATH
            . "/"
            . $file_infos[self::FILE_NAME]
        ;
        move_uploaded_file($file_infos[self::TMP_NAME], $target);
    }

    public function getImageName() : string
    {
        if (!$this->filesystem->hasDir(self::IMAGE_PATH)) {
            return "";
        }

        $files = $this->filesystem->listContents(self::IMAGE_PATH);
        if (count($files) == 0) {
            return "";
        }

        $file = array_shift($files);
        return str_replace(self::IMAGE_PATH . "/", "", $file->getPath());
    }

    public function getIncludePath() : string
    {
        if (!$this->filesystem->hasDir(self::IMAGE_PATH)) {
            return "";
        }

        $files = $this->filesystem->listContents(self::IMAGE_PATH);
        if (count($files) == 0) {
            return "";
        }

        $file = array_shift($files);
        return "./"
            . ILIAS_WEB_DIR
            . "/"
            . CLIENT_ID
            . "/"
            . $file->getPath()
        ;
    }
}

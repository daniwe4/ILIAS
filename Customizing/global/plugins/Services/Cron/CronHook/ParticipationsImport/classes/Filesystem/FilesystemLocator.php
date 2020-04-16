<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\Filesystem;

class FilesystemLocator implements Locator
{
    const INCREMENT_PLACEHOLDER = '[INCREMENT]';

    public function __construct(ConfigStorage $cs)
    {
        $this->cs = $cs;
    }

    public function getCurrentFilePath() : string
    {
        $cfg = $this->cs->loadCurrentConfig();

        $filetitle = str_replace(
            self::INCREMENT_PLACEHOLDER,
            '.*',
            $cfg->filetitleTemplate()
        );

        $results = preg_grep('#' . $filetitle . '#', $this->readDir($cfg->path()));
        rsort($results);
        $file = array_shift($results);
        if ($file) {
            return $this->canonicDirectoryPath($cfg->path()) . $file;
        }
        throw new NoFileFoundException(
            'Found no files subjected to template "' . $cfg->filetitleTemplate()
            . '" in folder ' . $cfg->path()
        );
    }

    protected function readDir(string $dir) : array
    {
        $dir_res = opendir($this->canonicDirectoryPath($dir));
        $files = [];
        while (false !== ($entry = readdir($dir_res))) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $files[] = $entry;
        }
        sort($files);
        closedir($dir_res);
        return $files;
    }

    protected function canonicDirectoryPath(string $dir)
    {
        return rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }
}

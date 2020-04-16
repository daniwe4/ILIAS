<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\Filesystem;

class Config
{
    protected $path;

    public function __construct(
        string $path,
        string $filetitle_template
    ) {
        $this->path = $path;
        $this->filetitle_template = $filetitle_template;
    }

    public function path() : string
    {
        return $this->path;
    }

    public function filetitleTemplate() : string
    {
        return $this->filetitle_template;
    }
}

<?php

declare(strict_types=1);

namespace ILIAS\TMS\Wizard;

class Content
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $body;

    public function __construct(string $title, string $body = '')
    {
        $this->title = $title;
        $this->body = $body;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getBody() : string
    {
        return $this->body;
    }
}

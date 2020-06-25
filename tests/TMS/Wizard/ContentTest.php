<?php

declare(strict_types=1);

namespace ILIAS\TMS\Wizard;

use PHPUnit\Framework\TestCase;

class ContentTest extends TestCase
{
    public function test_create_object()
    {
        $title = 'title';
        $body = 'body';

        $content = new Content($title, $body);

        $this->assertInstanceOf(Content::class, $content);
        $this->assertEquals($title, $content->getTitle());
        $this->assertEquals($body, $content->getBody());
    }
}

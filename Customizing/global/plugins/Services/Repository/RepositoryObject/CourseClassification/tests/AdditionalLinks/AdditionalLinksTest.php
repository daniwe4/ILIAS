<?php

declare(strict_types=1);

namespace CaT\Plugins\CourseClassification\AdditionalLinks;

use PHPUnit\Framework\TestCase;

class AdditionalLinksTest extends TestCase
{
    public function test_create_instance()
    {
        $al = new AdditionalLink('label', 'url');
        $this->assertInstanceOf(AdditionalLink::class, $al);
    }

    public function test_getter()
    {
        $al = new AdditionalLink('label', 'url');
        $this->assertInstanceOf(AdditionalLink::class, $al);
        $this->assertEquals('label', $al->getLabel());
        $this->assertEquals('url', $al->getUrl());
    }
}

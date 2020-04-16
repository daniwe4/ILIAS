<?php

declare(strict_types=1);

namespace CaT\Plugins\CourseClassification\Options;

use PHPUnit\Framework\TestCase;

class OptionsTest extends TestCase
{
    public function test_getProperties()
    {
        $option = new Option(10, "caption");

        $this->assertEquals(10, $option->getId());
        $this->assertEquals("caption", $option->getCaption());
    }

    public function test_with()
    {
        $option = new Option(10, "caption");
        $new_option = $option->withCaption("caption2");

        $this->assertEquals(10, $option->getId());
        $this->assertEquals("caption", $option->getCaption());

        $this->assertEquals(10, $new_option->getId());
        $this->assertEquals("caption2", $new_option->getCaption());
    }
}

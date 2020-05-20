<?php

declare(strict_types=1);

namespace CaT\Plugins\StatusMails\Course;

use PHPUnit\Framework\TestCase;

class CourseFlagsTest extends TestCase
{
    public function test_construction()
    {
        $cf = new CourseFlags(1, true, true);
        $this->assertInstanceOf(CourseFlags::class, $cf);
        $this->assertTrue($cf->preventMailEntirely());
        $this->assertTrue($cf->outlineOvernights());
    }

    public function test_construction_id()
    {
        try {
            $cf = new CourseFlags("12", true, true);
            $this->assertFalse("This should not happen");
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
    }
}

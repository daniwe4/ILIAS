<?php

declare(strict_types=1);

namespace CaT\Plugins\CourseClassification\Options\Topic;

use PHPUnit\Framework\TestCase;
use CaT\Plugins\CourseClassification\Options\Category\Category;

class TopicTest extends TestCase
{
    public function test_getProperties()
    {
        $category = new Category(10, "caption", ...[]);
        $topic = new Topic(10, "caption", $category);

        $this->assertEquals(10, $topic->getId());
        $this->assertEquals("caption", $topic->getCaption());
        $this->assertEquals($category, $topic->getCategory());
    }

    public function test_with()
    {
        $category = new Category(10, "caption", ...[]);
        $category2 = new Category(20, "caption", ...[]);
        $topic = new Topic(10, "caption", $category);

        $new_topic = $topic->withCaption("caption2");
        $this->assertEquals(10, $topic->getId());
        $this->assertEquals("caption", $topic->getCaption());
        $this->assertEquals($category, $topic->getCategory());

        $this->assertEquals(10, $new_topic->getId());
        $this->assertEquals("caption2", $new_topic->getCaption());
        $this->assertEquals($category, $new_topic->getCategory());

        $new_topic2 = $new_topic->withCategory($category2);
        $this->assertEquals(10, $new_topic->getId());
        $this->assertEquals("caption2", $new_topic->getCaption());
        $this->assertEquals($category, $new_topic->getCategory());

        $this->assertEquals(10, $new_topic2->getId());
        $this->assertEquals("caption2", $new_topic2->getCaption());
        $this->assertEquals($category2, $new_topic2->getCategory());
    }
}

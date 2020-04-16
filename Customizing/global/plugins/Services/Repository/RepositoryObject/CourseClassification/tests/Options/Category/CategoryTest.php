<?php

declare(strict_types=1);

namespace CaT\Plugins\CourseClassification\Options\Category;

use PHPUnit\Framework\TestCase;
use CaT\Plugins\CourseClassification\Options\Topic\Topic;

class CategoryTest extends TestCase
{
    public function test_getProperties()
    {
        $topics = array(
            new Topic(1, "test1"),
            new Topic(2, "test2"),
            new Topic(3, "test3"),
            new Topic(4, "test4")
        );
        $category = new Category(10, "caption", ...$topics);

        $this->assertEquals(10, $category->getId());
        $this->assertEquals("caption", $category->getCaption());
        $this->assertEquals($topics, $category->getTopics());
    }

    public function test_with()
    {
        $topics = array(
            new Topic(1, "test1"),
            new Topic(2, "test2"),
            new Topic(3, "test3"),
            new Topic(4, "test4")
        );
        $topics2 = array(
            new Topic(5, "test5"),
            new Topic(6, "test6"),
            new Topic(7, "test7"),
            new Topic(8, "test8")
        );
        $category = new Category(10, "caption", ...$topics);

        $new_category = $category->withCaption("caption2");
        $this->assertEquals(10, $category->getId());
        $this->assertEquals("caption", $category->getCaption());
        $this->assertEquals($topics, $category->getTopics());

        $this->assertEquals(10, $new_category->getId());
        $this->assertEquals("caption2", $new_category->getCaption());
        $this->assertEquals($topics, $new_category->getTopics());

        $new_category2 = $new_category->withTopics(...$topics2);
        $this->assertEquals(10, $new_category->getId());
        $this->assertEquals("caption2", $new_category->getCaption());
        $this->assertEquals($topics, $new_category->getTopics());

        $this->assertEquals(10, $new_category2->getId());
        $this->assertEquals("caption2", $new_category2->getCaption());
        $this->assertEquals($topics2, $new_category2->getTopics());
    }
}

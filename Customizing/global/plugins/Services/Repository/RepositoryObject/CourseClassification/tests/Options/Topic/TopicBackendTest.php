<?php

declare(strict_types=1);

namespace CaT\Plugins\CourseClassification\Options\Topic;

use CaT\Plugins\CourseClassification\Options\Category\Category;
use PHPUnit\Framework\TestCase;

class TopicBackendTest extends TestCase
{
    public function test_create_object()
    {
        $backend = new TopicBackend($this->getActionsObject());
        $this->assertInstanceOf(TopicBackend::class, $backend);
    }

    public function test_create()
    {
        $cat_id = 10;
        $cat_caption = 'Great category';
        $category = new Category($cat_id, $cat_caption, ...[]);

        $id = -1;
        $n_id = 20;
        $caption = 'My Topic';
        $topic = new Topic($id, $caption, $category);
        $new_topic = new Topic($n_id, $caption, $category);
        $record = ['option' => $topic];

        $actions = $this->getActionsObject();
        $actions->expects($this->once())
            ->method('create')
            ->with($caption)
            ->willReturn($new_topic)
        ;
        $actions->expects($this->once())
            ->method('assignCategory')
            ->with($n_id, $cat_id)
        ;
        $backend = new TopicBackend($actions);
        $this->assertInstanceOf(TopicBackend::class, $backend);
        $n_record = $backend->create($record);

        $this->assertNotSame($topic, $n_record['option']);
        $this->assertTrue(in_array('created_succesfull', $n_record['message']));
    }

    public function test_update()
    {
        $cat_id = 25;
        $cat_caption = 'Greatest category ever';
        $category = new Category($cat_id, $cat_caption, ...[]);

        $id = 20;
        $caption = 'My Topic';
        $topic = new Topic($id, $caption, $category);
        $record = ['option' => $topic];

        $actions = $this->getActionsObject();
        $actions->expects($this->once())
            ->method('assignedCategoriesFor')
            ->with($id)
            ->willReturn([321])
        ;
        $actions->expects($this->once())
            ->method('update')
            ->with($topic)
        ;
        $actions->expects($this->once())
            ->method('deassignCategory')
            ->with($id)
        ;
        $actions->expects($this->once())
            ->method('assignCategory')
            ->with($id, $cat_id)
        ;
        $backend = new TopicBackend($actions);
        
        $n_record = $backend->update($record);
        $this->assertSame($topic, $n_record['option']);
        $this->assertTrue(in_array('category_changed', $n_record['message']));
        $this->assertTrue(in_array('update_succesfull', $n_record['message']));
    }

    protected function getActionsObject() : ilActions
    {
        return $this->createMock(ilActions::class);
    }
}

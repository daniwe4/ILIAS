<?php

declare(strict_types=1);

namespace CaT\Plugins\CourseClassification\Options;

use PHPUnit\Framework\TestCase;

class OptionBackendTest extends TestCase
{
    public function test_create_instance()
    {
        $backend = new OptionBackend($this->getActions());
        $this->assertInstanceOf(OptionBackend::class, $backend);
    }

    public function test_create()
    {
        $id = -1;
        $n_id = 24;
        $caption = 'New option';
        $option = new Option($id, $caption);
        $n_option = new Option($n_id, $caption);
        $record['option'] = $option;
        $actions = $this->getActions();
        $actions->expects($this->once())
            ->method('create')
            ->with($caption)
            ->willReturn($n_option)
        ;

        $backend = new OptionBackend($actions);
        $n_record = $backend->create($record);
        $this->assertNotSame($option, $n_record['option']);
    }

    public function test_valid()
    {
        $id = 24;
        $caption = 'New option';
        $option = new Option($id, $caption);
        $option_no_caption = new Option($id, "");
        $record['option'] = $option;
        $record_2['option'] = $option_no_caption;

        $backend = new OptionBackend($this->getActions());
        $result = $backend->valid($record);
        $this->assertFalse(isset($result["errors"]["caption"]));

        $result_2 = $backend->valid($record_2);
        $this->assertTrue(isset($result_2["errors"]["caption"]));
        $this->assertTrue(in_array('name_empty', $result_2["errors"]["caption"]));
    }

    public function test_update()
    {
        $id = 55;
        $caption = 'update option';
        $option = new Option($id, $caption);
        $record['option'] = $option;

        $actions = $this->getActions();
        $actions->expects($this->once())
            ->method('update')
            ->with($option)
        ;

        $backend = new OptionBackend($actions);
        $result = $backend->update($record);

        $this->assertTrue(in_array('update_succesfull', $result["message"]));
    }

    public function test_delete()
    {
        $id = 623;
        $caption = 'to delete option';
        $option = new Option($id, $caption);
        $record['option'] = $option;

        $actions = $this->getActions();
        $actions->expects($this->once())
            ->method('delete')
            ->with($id)
        ;

        $backend = new OptionBackend($actions);
        $backend->delete($record);
    }

    public function getActions() : ilActions
    {
        return $this->createMock(ilActions::class);
    }
}

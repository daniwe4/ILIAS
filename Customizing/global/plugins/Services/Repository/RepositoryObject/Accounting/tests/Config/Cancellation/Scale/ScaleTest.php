<?php

declare(strict_types=1);

namespace CaT\Plugins\Accounting\Config\Cancellation\Scale;

use PHPUnit\Framework\TestCase;

class ScaleTest extends TestCase
{
    public function test_create_instance()
    {
        $id = 1;
        $span_start = 10;
        $span_end = 13;
        $percent = 5;

        $obj = new Scale($id, $span_start, $span_end, $percent);
        $this->assertInstanceOf(Scale::class, $obj);
    }

    public function test_getter()
    {
        $id = 1;
        $span_start = 10;
        $span_end = 13;
        $percent = 5;

        $obj = new Scale($id, $span_start, $span_end, $percent);
        $this->assertEquals($id, $obj->getId());
        $this->assertEquals($span_start, $obj->getSpanStart());
        $this->assertEquals($span_end, $obj->getSpanEnd());
        $this->assertEquals($percent, $obj->getPercent());
    }

    public function test_clone_with_span_start()
    {
        $id = 1;
        $span_start = 10;
        $span_end = 13;
        $percent = 5;

        $n_span_start = 12;

        $obj = new Scale($id, $span_start, $span_end, $percent);
        $n_obj = $obj->withSpanStart($n_span_start);

        $this->assertEquals($id, $obj->getId());
        $this->assertEquals($span_start, $obj->getSpanStart());
        $this->assertEquals($span_end, $obj->getSpanEnd());
        $this->assertEquals($percent, $obj->getPercent());

        $this->assertEquals($id, $n_obj->getId());
        $this->assertEquals($n_span_start, $n_obj->getSpanStart());
        $this->assertEquals($span_end, $n_obj->getSpanEnd());
        $this->assertEquals($percent, $n_obj->getPercent());

        $this->assertNotSame($obj, $n_obj);
    }

    public function test_clone_with_span_end()
    {
        $id = 1;
        $span_start = 10;
        $span_end = 13;
        $percent = 5;

        $n_span_end = 12;

        $obj = new Scale($id, $span_start, $span_end, $percent);
        $n_obj = $obj->withSpanEnd($n_span_end);

        $this->assertEquals($id, $obj->getId());
        $this->assertEquals($span_start, $obj->getSpanStart());
        $this->assertEquals($span_end, $obj->getSpanEnd());
        $this->assertEquals($percent, $obj->getPercent());

        $this->assertEquals($id, $n_obj->getId());
        $this->assertEquals($span_start, $n_obj->getSpanStart());
        $this->assertEquals($n_span_end, $n_obj->getSpanEnd());
        $this->assertEquals($percent, $n_obj->getPercent());

        $this->assertNotSame($obj, $n_obj);
    }

    public function test_clone_with_percent()
    {
        $id = 1;
        $span_start = 10;
        $span_end = 13;
        $percent = 5;

        $n_percent = 30;

        $obj = new Scale($id, $span_start, $span_end, $percent);
        $n_obj = $obj->withPercent($n_percent);

        $this->assertEquals($id, $obj->getId());
        $this->assertEquals($span_start, $obj->getSpanStart());
        $this->assertEquals($span_end, $obj->getSpanEnd());
        $this->assertEquals($percent, $obj->getPercent());

        $this->assertEquals($id, $n_obj->getId());
        $this->assertEquals($span_start, $n_obj->getSpanStart());
        $this->assertEquals($span_end, $n_obj->getSpanEnd());
        $this->assertEquals($n_percent, $n_obj->getPercent());

        $this->assertNotSame($obj, $n_obj);
    }
}

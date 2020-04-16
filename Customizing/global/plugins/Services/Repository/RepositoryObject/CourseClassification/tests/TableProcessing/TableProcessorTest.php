<?php

declare(strict_types=1);

namespace CaT\Plugins\CourseClassification\TableProcessing;

use PHPUnit\Framework\TestCase;
use CaT\Plugins\CourseClassification\Options\Option;

class TableProcessorTest extends TestCase
{
    public function test_delete()
    {
        $records = [
                ["option" => new Option(-1, "caption"), "delete" => true, "errors" => []],
                ["option" => new Option(1, "caption"), "delete" => true, "errors" => []],
                ["option" => new Option(2, "caption"), "delete" => true, "errors" => []]
        ];

        $backend = $this->createMock(backend::class);
        $backend->expects($this->exactly(2))
                ->method("delete");

        $backend->expects($this->exactly(0))
                ->method("valid");

        $backend->expects($this->exactly(0))
                ->method("update");

        $backend->expects($this->exactly(0))
                ->method("create");

        $processor = new TableProcessor($backend);
        $processor->process($records, [TableProcessor::ACTION_DELETE]);
    }

    public function test_updateNCreate()
    {
        $records = [
                ["option" => new Option(-1, "caption"), "delete" => false, "errors" => []],
                ["option" => new Option(1, "caption"), "delete" => false, "errors" => []],
                ["option" => new Option(2, "caption"), "delete" => false, "errors" => []]
        ];

        $backend = $this->createMock(backend::class);
        $backend->expects($this->exactly(0))
                ->method("delete");

        $backend->expects($this->exactly(3))
                ->method("valid")
                ->will($this->onConsecutiveCalls(
                    ["option" => new Option(-1, "caption"), "delete" => false, "errors" => []],
                    ["option" => new Option(1, "caption"), "delete" => false, "errors" => ["A", "B"]],
                    ["option" => new Option(2, "caption"), "delete" => false, "errors" => []]
                ));

        $backend->expects($this->exactly(1))
                ->method("update");

        $backend->expects($this->exactly(1))
                ->method("create");

        $processor = new TableProcessor($backend);
        $processor->process($records, [TableProcessor::ACTION_SAVE]);
    }
}

<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */
/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

use ILIAS\TMS;
use PHPUnit\Framework\TestCase;

require_once(__DIR__ . "/../../Services/UICore/classes/class.ilCtrl.php");
require_once(__DIR__ . "/../../Services/User/classes/class.ilObjUser.php");

class ActionBuilderUserHelperTest
{
    use TMS\ActionBuilderUserHelper;

    public function getComponentsOfType($component_type)
    {
        throw new \LogicException("mock me");
    }
}

class TMS_ActionBuilderUserHelperTest extends TestCase
{
    public function test_getCourseInfo()
    {
        $helper = $this
            ->getMockBuilder(ActionBuilderUserHelperTest::class)
            ->setMethods(["getComponentsOfType"])
            ->getMock();

        $component1 = $this->createMock("\\ILIAS\\TMS\\ActionBuilder");
        $component2 = $this->createMock("\\ILIAS\\TMS\\ActionBuilder");
        $component3 = $this->createMock("\\ILIAS\\TMS\\ActionBuilder");

        $helper
            ->expects($this->once())
            ->method("getComponentsOfType")
            ->willReturn([$component1, $component2, $component3]);

        $action_builders = $helper->getActionBuilders();
        $this->assertEquals([$component1, $component2, $component3], $action_builders);
    }
}

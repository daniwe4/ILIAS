<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

require_once(__DIR__ . "/../../Services/Object/classes/class.ilObject.php");
require_once(__DIR__ . "/../../Services/User/classes/class.ilObjUser.php");

use ILIAS\TMS;
use PHPUnit\Framework\TestCase;

class _CourseActionImpl extends TMS\CourseActionImpl
{
    public function getLink(\ilCtrl $ctrl, $usr_id)
    {
        throw new Exception("mock me");
    }
    public function isAllowedFor($usr_id)
    {
        throw new Exception("mock me");
    }
    public function getLabel()
    {
        throw new Exception("mock me");
    }
}

class CourseActionTest extends TestCase
{
    public function test_fields()
    {
        $priority = 10;
        $entity = $this->createMock(CaT\Ente\Entity::class);
        $owner = $this->createMock(ilObject::class);
        $user = $this->createMock(\ilObjUser::class);

        $user
            ->expects($this->once())
            ->method("getId");

        $action = new _CourseActionImpl($entity, $owner, $user, $priority);

        $this->assertEquals($entity, $action->getEntity());
        $this->assertEquals($owner, $action->getOwner());
        $this->assertEquals($priority, $action->getPriority());
    }
}

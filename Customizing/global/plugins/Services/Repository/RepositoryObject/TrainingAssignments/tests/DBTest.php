<?php

use PHPUnit\Framework\TestCase;
use CaT\Plugins\TrainingAssignments\AssignedTrainings\AssignedTraining;
use CaT\Plugins\TrainingAssignments\AssignedTrainings\ilDB;
use ILIAS\TMS;
use ILIAS\UI;

/**
 * @group needsInstalledILIAS
 */
class DBTest extends TestCase
{
    public function setUp() : void
    {
        if (!interface_exists("ilDBInterface")) {
            require_once(__DIR__ . "/ilDBInterface.php");
        }
        $this->db = $this->createMock("\ilDBInterface");
    }

    public function test_getCourses()
    {
        $crs_ids = array(1,2,3);
        $date = new ilDateTime(time(), IL_CAL_UNIX);
        $at = new AssignedTraining(1, "title", $date);
        $at2 = new AssignedTraining(3, "title", $date);

        $db = $this->getMockBuilder(ilDB::class)
            ->setMethods(["getCourseIdsWhereUserIsTutor", "showTraining", "getRefId", "createAssignedTraining"])
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->getMock();

        $db ->expects($this->once())
            ->method("getCourseIdsWhereUserIsTutor")
            ->willReturn($crs_ids);

        $db ->expects($this->exactly(3))
            ->method("showTraining")
            ->withConsecutive([1], [2], [3])
            ->will($this->onConsecutiveCalls(true, false, true));

        $db ->expects($this->exactly(2))
            ->method("getRefId")
            ->withConsecutive([1], [3])
            ->will($this->onConsecutiveCalls(5, 7));

        $db ->expects($this->exactly(2))
            ->method("createAssignedTraining")
            ->withConsecutive([5], [7])
            ->will($this->onConsecutiveCalls($at, $at2));

        $this->assertEquals(array($at, $at2), $db->getAssignedTrainingsFor(10));
    }
}

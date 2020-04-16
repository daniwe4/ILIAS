<?php

use PHPUnit\Framework\TestCase;
use CaT\Plugins\TrainingAssignments\AssignedTrainings\AssignedTraining;
use ILIAS\TMS;
use ILIAS\UI;

/**
 * @group needsInstalledILIAS
 */
class AssignedTrainingsTest extends TestCase
{
    public function test_create()
    {
        require_once("Services/Calendar/classes/class.ilDateTime.php");
        $date = new ilDateTime(time(), IL_CAL_UNIX);
        $at = new AssignedTraining(10, "title", $date);
        $this->assertEquals(10, $at->getRefId());
        $this->assertSame($date, $at->getCourseStartDate());
    }

    public function test_getTitleUnknown()
    {
        $at = $this->getMockBuilder(AssignedTraining::class)
            ->setMethods(["getImportantInfos", "getUnknownString"])
            ->disableOriginalConstructor()
            ->getMock();

        $at ->expects($this->once())
            ->method("getImportantInfos")
            ->willReturn(array());

        $at ->expects($this->once())
            ->method("getUnknownString")
            ->willReturn("unknown");

        $this->assertEquals("unknown", $at->getTitle());
    }

    public function test_getTitle()
    {
        $label = "LABEL";
        $value = "VALUE";
        $description = "DESCRIPTION";
        $priority = 10;
        $contexts = [TMS\CourseInfo::CONTEXT_SEARCH_SHORT_INFO];
        $entity = $this->createMock(CaT\Ente\Entity::class);
        $info = new TMS\CourseInfoImpl($entity, $label, $value, $description, $priority, $contexts);

        $at = $this->getMockBuilder(AssignedTraining::class)
            ->setMethods(["getImportantInfos", "getUnknownString"])
            ->disableOriginalConstructor()
            ->getMock();

        $at ->expects($this->once())
            ->method("getImportantInfos")
            ->willReturn(array($info));

        $at ->expects($this->never())
            ->method("getUnknownString");

        $this->assertEquals("VALUE", $at->getTitle());
    }

    public function test_getImportantEmpty()
    {
        $at = $this->getMockBuilder(AssignedTraining::class)
            ->setMethods(["getImportantInfos", "unpackValue"])
            ->disableOriginalConstructor()
            ->getMock();

        $at ->expects($this->once())
            ->method("getImportantInfos")
            ->willReturn(array());

        $at ->expects($this->once())
            ->method("unpackValue")
            ->willReturn(array());

        $this->assertEquals(array(), $at->getImportantValue());
    }

    public function test_getImportant()
    {
        $label = "LABEL";
        $value = "VALUE";
        $description = "DESCRIPTION";
        $priority = 10;
        $contexts = [TMS\CourseInfo::CONTEXT_SEARCH_SHORT_INFO];
        $entity = $this->createMock(CaT\Ente\Entity::class);
        $info = new TMS\CourseInfoImpl($entity, $label, $value, $description, $priority, $contexts);
        $label2 = "LABEL2";
        $value2 = "VALUE2";
        $description2 = "DESCRIPTION2";
        $priority2 = 20;
        $info2 = new TMS\CourseInfoImpl($entity, $label2, $value2, $description2, $priority2, $contexts);

        $at = $this->getMockBuilder(AssignedTraining::class)
            ->setMethods(["getImportantInfos", "unpackValue"])
            ->disableOriginalConstructor()
            ->getMock();

        $at ->expects($this->once())
            ->method("getImportantInfos")
            ->willReturn(array($info, $info2));

        $at ->expects($this->once())
            ->method("unpackValue")
            ->willReturn(array($value2));

        $this->assertEquals(array($value2), $at->getImportantValue());
    }

    public function test_getNoContent()
    {
        $at = $this->getMockBuilder(AssignedTraining::class)
            ->setMethods(["getContentInfos", "getNoDetailInfoMessage"])
            ->disableOriginalConstructor()
            ->getMock();

        $at ->expects($this->once())
            ->method("getContentInfos")
            ->willReturn(array());

        $at ->expects($this->once())
            ->method("getNoDetailInfoMessage")
            ->willReturn("unknown");

        $this->assertEquals(array("" => "unknown"), $at->getContent());
    }

    public function test_getContent()
    {
        $label = "LABEL";
        $value = "VALUE";
        $description = "DESCRIPTION";
        $priority = 10;
        $contexts = [TMS\CourseInfo::CONTEXT_USER_BOOKING_DETAIL_INFO];
        $entity = $this->createMock(CaT\Ente\Entity::class);
        $info = new TMS\CourseInfoImpl($entity, $label, $value, $description, $priority, $contexts);
        $label2 = "LABEL2";
        $value2 = "VALUE2";
        $description2 = "DESCRIPTION2";
        $priority2 = 20;
        $info2 = new TMS\CourseInfoImpl($entity, $label2, $value2, $description2, $priority2, $contexts);
        $factory = $this->createMock(UI\Factory::class);

        $at = $this->getMockBuilder(AssignedTraining::class)
            ->setMethods(["getContentInfos", "getNoDetailInfoMessage", "unpackLabelAndNestedValue", "getUIFactory"])
            ->disableOriginalConstructor()
            ->getMock();

        $at ->expects($this->once())
            ->method("getContentInfos")
            ->willReturn(array($info, $info2));

        $at ->expects($this->once())
            ->method("getUIFactory")
            ->willReturn($factory);

        $at ->expects($this->once())
            ->method("unpackLabelAndNestedValue")
            ->willReturn(array($label => $value, $label2 => $value2));

        $at ->expects($this->never())
            ->method("getNoDetailInfoMessage");

        $this->assertEquals(array($label => $value, $label2 => $value2), $at->getContent());
    }

    public function test_getNoFurtherFields()
    {
        $factory = $this->createMock(UI\Factory::class);

        $at = $this->getMockBuilder(AssignedTraining::class)
            ->setMethods(["getFurtherInfo", "unpackLabelAndNestedValue", "getUIFactory"])
            ->disableOriginalConstructor()
            ->getMock();

        $at ->expects($this->once())
            ->method("getFurtherInfo")
            ->willReturn(array());

        $at ->expects($this->once())
            ->method("getUIFactory")
            ->willReturn($factory);

        $at ->expects($this->once())
            ->method("unpackLabelAndNestedValue")
            ->willReturn(array());

        $this->assertEquals(array(), $at->getFurtherFields());
    }

    public function test_getFurtherFields()
    {
        $label = "LABEL";
        $value = "VALUE";
        $description = "DESCRIPTION";
        $priority = 10;
        $contexts = [CourseInfo::CONTEXT_ASSIGNED_TRAINING_FURTHER_INFO];
        $entity = $this->createMock(CaT\Ente\Entity::class);
        $info = new TMS\CourseInfoImpl($entity, $label, $value, $description, $priority, $contexts);
        $label2 = "LABEL2";
        $value2 = "VALUE2";
        $description2 = "DESCRIPTION2";
        $priority2 = 20;
        $info2 = new TMS\CourseInfoImpl($entity, $label2, $value2, $description2, $priority2, $contexts);
        $factory = $this->createMock(UI\Factory::class);

        $at = $this->getMockBuilder(AssignedTraining::class)
            ->setMethods(["getFurtherInfo", "unpackLabelAndNestedValue", "getUIFactory"])
            ->disableOriginalConstructor()
            ->getMock();

        $at ->expects($this->once())
            ->method("getFurtherInfo")
            ->willReturn(array($info, $info2));

        $at ->expects($this->once())
            ->method("getUIFactory")
            ->willReturn($factory);

        $at ->expects($this->once())
            ->method("unpackLabelAndNestedValue")
            ->willReturn(array($label => $value, $label2 => $value2));

        $this->assertEquals(array($label => $value, $label2 => $value2), $at->getFurtherFields());
    }
}

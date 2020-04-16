<?php

use PHPUnit\Framework\TestCase;


use CaT\Plugins\CourseMailing\Surroundings;
use ILIAS\TMS\Mailing\Actions;

/**
 * @group needsInstalledILIAS
 */
class SurroundingsTest extends TestCase
{
    public function setUp() : void
    {
        require_once 'Services/PHPUnit/classes/class.ilUnitUtil.php';
        ilUnitUtil::performInitialisation();
        require_once 'Services/Mail/classes/class.ilMailTemplate.php';
    }

    public function test_init()
    {
        $sur = new Surroundings\Surroundings(
            $this->getCourseAccessorMock(),
            $this->getMailsAccessorMock()
        );
        $this->assertInstanceOf(Surroundings\Surroundings::class, $sur);
    }

    /**
     * @depends test_init
     */
    public function test_local_roles()
    {
        $sur = new Surroundings\Surroundings(
            $this->getCourseAccessorMock([], [1 => 'r1',2 => 'r2']),
            $this->getMailsAccessorMock()
        );
        $this->assertEquals($sur->getLocalRoles(), [1 => 'r1',2 => 'r2']);
    }


    /**
     * @depends test_init
     */
    public function test_mail_templates()
    {
        require_once 'Services/Mail/classes/class.ilMailTemplate.php';
        $t1 = new ilMailTemplate(['context' => 'c1']);
        $t2 = new ilMailTemplate(['context' => 'c2']);
        $t3 = new ilMailTemplate(['context' => 'c3']);
        $sur = new Surroundings\Surroundings(
            $this->getCourseAccessorMock(),
            $this->getMailsAccessorMock(
                [
                    [['c1','c2'],[$t1,$t2]]
                    ,[['c3'],[$t3]]
                ]
            )
        );
        $mail_templates = $sur->getMailTemplates(['c1','c2']);
        foreach ($mail_templates as $template) {
            $this->assertInstanceOf(ilMailTemplate::class, $template);
            $this->assertContains($template->getContext(), ['c1','c2']);
        }
    }

    /**
     * @depends test_init
     */
    public function test_mail_template()
    {
        require_once 'Services/Mail/classes/class.ilMailTemplate.php';
        $t1 = new ilMailTemplate(['tpl_id' => 1]);
        $t2 = new ilMailTemplate(['tpl_id' => 2]);
        $t3 = new ilMailTemplate(['tpl_id' => 3]);
        $sur = new Surroundings\Surroundings(
            $this->getCourseAccessorMock(),
            $this->getMailsAccessorMock([], [[1,$t1],[2,$t2],[3,$t3]])
        );
        $mail_template = $sur->getMailTemplate(2);
        $this->assertInstanceOf(ilMailTemplate::class, $mail_template);
        $this->assertEquals($mail_template->getTplId(), 2);
    }


    /**
     * @depends test_init
     */
    public function test_parent_course_ref_id()
    {
        $sur = new Surroundings\Surroundings(
            $this->getCourseAccessorMock(['ref_id' => 2]),
            $this->getMailsAccessorMock()
        );
        $this->assertEquals($sur->getParentCourseRefId(), 2);
    }

    /**
     * @depends test_init
     */
    public function test_mail_template_dat_by_ident()
    {
        $d1 = ['context' => Actions::BOOKED_ON_COURSE];
        $d2 = ['context' => Actions::BOOKED_ON_WAITINGLIST];
        $d3 = ['context' => Actions::CANCELED_FROM_COURSE];
        $d4 = ['context' => Actions::CANCELED_FROM_WAITINGLIST];
        $sur = new Surroundings\Surroundings(
            $this->getCourseAccessorMock(),
            $this->getMailsAccessorMock(
                [],
                [],
                [],
                [
                    [Actions::BOOKED_ON_COURSE,$d1]
                    ,[Actions::BOOKED_ON_WAITINGLIST,$d2]
                    ,[Actions::CANCELED_FROM_COURSE,$d3]
                    ,[Actions::CANCELED_FROM_WAITINGLIST,$d4]
                ]
            )
        );
        $mail_template_data = $sur->getMailTemplateDataByIdent(Actions::CANCELED_FROM_COURSE);
        $this->assertEquals($mail_template_data['context'], Actions::CANCELED_FROM_COURSE);
    }

    /**
     * @depends test_parent_course_ref_id
     * @expectedException \LogicException
     */
    public function test_parent_course_ref_id_error()
    {
        $sur = new Surroundings\Surroundings(
            $this->getCourseAccessorMock(false),
            $this->getMailsAccessorMock()
        );
        $sur->getParentCourseRefId();
    }

    /**
     * @depends test_init
     */
    public function test_members_of_parent_course()
    {
        require_once 'Services/User/classes/class.ilObjUser.php';
        $u1 = new ilObjUser();
        $u1->setId(1);
        $u2 = new ilObjUser();
        $u2->setId(2);
        $sur = new Surroundings\Surroundings(
            $this->getCourseAccessorMock([], [], [$u1,$u2]),
            $this->getMailsAccessorMock()
        );
        $usrs = $sur->getMembersOfParentCourse();
        $this->assertCount(2, $usrs);
        foreach ($usrs as $usr) {
            $this->assertInstanceOf(\ilObjUser::class, $usr);
            $this->assertContains($usr->getId(), [1,2]);
        }
    }

    /**
     * @depends test_init
     */
    public function test_course_member_ids_with_role()
    {
        $sur = new Surroundings\Surroundings(
            $this->getCourseAccessorMock([], [], [], [[1,[11,21]],[2,[21,22]],[3,[31,32]]]),
            $this->getMailsAccessorMock()
        );
        $mem_ids = $sur->getCourseMemberIdsWithRole(2);
        $this->assertCount(2, $mem_ids);
        foreach ($mem_ids as $mem_id) {
            $this->assertContains($mem_id, [21,22]);
        }
    }

    protected function getCourseAccessorMock(
        $parent_crs_info = [],
        array $local_roles = [],
        array $members_of_parent = [],
        array $members_with_role = []
    ) {
        $mock = $this->createMock(Surroundings\ilCourseAccessor::class);
        $mock->method('getParentCourseInfo')->willReturn($parent_crs_info);
        $mock->method('getLocalRoles')->willReturn($local_roles);
        $mock->method('getMembersOfParentCourse')->willReturn($members_of_parent);
        $mock->method('getMembersWithRole')->will($this->returnValueMap($members_with_role));
        return $mock;
    }

    protected function getMailsAccessorMock(
        array $templates = []	//by contexts
        ,
        array $template = []	//by id
        ,
        array $mail_template = []		//by ident
        ,
        array $mail_template_data = []		//by ident
    ) {
        $mock = $this->createMock(Surroundings\ilMailsAccessor::class);
        $mock->method('getTemplates')->will($this->returnValueMap($templates));
        $mock->method('getTemplate')->will($this->returnValueMap($template));
        $mock->method('getMailTemplateByIdent')->will($this->returnValueMap($mail_template));
        $mock->method('getMailTemplateDataByIdent')->will($this->returnValueMap($mail_template_data));
        return $mock;
    }
}

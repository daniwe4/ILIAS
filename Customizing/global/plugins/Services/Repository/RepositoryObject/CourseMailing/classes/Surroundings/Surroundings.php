<?php

namespace CaT\Plugins\CourseMailing\Surroundings;

use CaT\Plugins\CourseMailing\Surroundings\ilCourseAccessor;
use CaT\Plugins\CourseMailing\Surroundings\ilMailsAccessor;
use CaT\Ente\ILIAS\ilHandlerObjectHelper;
use \ILIAS\TMS\File;

/**
 * The plugin needs information about its environment,
 * i.e. get data from other parts of the system
 */
class Surroundings
{
    use ilHandlerObjectHelper;

    /**
     * @var Surroundings\ilCourseAccessor
     */
    protected $course_accessor;

    /**
     * @var
     */
    protected $mails_accessor;


    public function __construct(
        ilCourseAccessor $course_accessor,
        ilMailsAccessor $mails_accessor
    ) {
        $this->course_accessor = $course_accessor;
        $this->mails_accessor = $mails_accessor;
    }

    /**
     * @inheritdoc
     */
    protected function getEntityRefId()
    {
        return $this->getParentCourseRefId();
    }

    /**
     * @inheritdoc
     */
    protected function getDIC()
    {
        return $GLOBALS["DIC"];
    }

    /**
     * Get a list of local roles available at the course.
     *
     * @return array<string,string> 	role_id->role_title
     */
    public function getLocalRoles()
    {
        return $this->course_accessor->getLocalRoles();
    }

    /**
     * Get all mail templates (for context).
     *
     * @param string[] $contexts
     * @return ilMailTemplate[]
     */
    public function getMailTemplates($contexts)
    {
        return $this->mails_accessor->getTemplates($contexts);
    }

    /**
     * Get a single mail template by id
     *
     * @param int $id
     * @return \ilMailTemplate
     */
    public function getMailTemplate($id)
    {
        return $this->mails_accessor->getTemplate($id);
    }

    /**
     * @return int
     */
    public function getParentCourseRefId()
    {
        $info = $this->course_accessor->getParentCourseInfo();
        if (!$info) {
            throw new \LogicException('not in course', 1);
        }
        return (int) $info['ref_id'];
    }

    /**
     * @return int
     */
    public function getParentCourseId()
    {
        $info = $this->course_accessor->getParentCourseInfo();
        if (!$info) {
            throw new \LogicException('not in course', 1);
        }
        return (int) $info['obj_id'];
    }

    /**
     * Get data of a single mail template.
     *
     * @param string $ident
     * @return array<string, string>
     */
    public function getMailTemplateDataByIdent($ident)
    {
        return $this->mails_accessor->getMailTemplateDataByIdent($ident);
    }

    /**
     * Get all members of parent course
     *
     * @return ilObjUser[]
     */
    public function getMembersOfParentCourse()
    {
        return $this->course_accessor->getMembersOfParentCourse();
    }

    /**
     * Get all member-ids with a certain role.
     *
     * @param int 	$role_id
     * @return int[]
     */
    public function getCourseMemberIdsWithRole($role_id)
    {
        assert('is_int($role_id)');
        return $this->course_accessor->getMembersWithRole($role_id);
    }

    /**
     * Get all potential downloadables/export file via ente.
     *
     * @return File[]
     */
    public function getFilesForCourse()
    {
        $components = $this->getComponentsOfType(File::class);
        return $components;
    }

    /**
     * get the user's role at the course.
     *
     * @param int 	$usr_id
     * @return array
     */
    public function getRolesForMember(int $usr_id)
    {
        return $this->course_accessor->getRolesForMember($usr_id);
    }

    /**
     * Check is user on waiting list
     *
     * @param int $user_id
     * @return bool
     */
    public function isOnWaitingList(int $usr_id)
    {
        return $this->course_accessor->isOnWaitingList($usr_id);
    }

    /**
     * get the user's role at the course.
     *
     * @param int 	$usr_id
     * @return array
     */
    public function getRoleIdsOfUser(int $usr_id)
    {
        return $this->course_accessor->getRoleIdsForMember($usr_id);
    }

    public function getRoleSortingFor($usr_id)
    {
        return $this->course_accessor->getRoleSortingFor($usr_id);
    }
}

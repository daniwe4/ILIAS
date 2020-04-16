<?php

namespace CaT\Plugins\CourseMailing;

use CaT\Plugins\CourseMailing\Surroundings;
use CaT\Plugins\CourseMailing\RoleMapping;
use CaT\Plugins\CourseMailing\AutomaticMails\CourseMailHandler;
use ILIAS\TMS\Mailing;

/**
 * Communication class between front- and backend.
 * E.g. GUI only use this class to get information from ILIAS DB.
 */
class ilActions
{
    /**
     * @var \ilObjCourseMailing
     */
    protected $object;

    /**
     * @var Surroundings\Surroundings
     */
    protected $surroundings;

    /**
     * @var RoleMapping\DB
     */
    protected $mappings_db;

    /**
     * @var Settings\DB
     */
    protected $settings_db;

    /**
     * @var Mailing\LoggingDB
     */
    protected $logs_db;

    /**
     * @var Mailing\TMSMailClerk
     */
    protected $clerk;


    public function __construct(
        ObjCourseMailing $object,
        Surroundings\Surroundings $surroundings,
        RoleMapping\DB $mappings_db,
        Settings\DB $settings_db,
        Mailing\LoggingDB $logs_db,
        Mailing\TMSMailClerk $clerk
    ) {
        $this->object = $object;
        $this->surroundings = $surroundings;
        $this->mappings_db = $mappings_db;
        $this->settings_db = $settings_db;
        $this->logs_db = $logs_db;
        $this->clerk = $clerk;
    }

    /**
     * Get the object's id
     * @return int
     */
    public function getObjectId()
    {
        return (int) $this->object->getId();
    }

    /**
     * Get ObjCourseMailing
     *
     * @return \ilObjCourseMailing
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Get the plugin's directory
     * @return string
     */
    public function getPluginDirectory()
    {
        return $this->object->getPluginDirectory();
    }

    /**
     * Get a list of local roles available at the course.
     *
     * @return array<string,string> 	role_id->role_title
     */
    public function getAvailableLocalRoles()
    {
        return $this->surroundings->getLocalRoles();
    }

    /**
     * Get mail templates.
     *
     * @param string[] | [] $contexts
     * @return ilMailTemplate[]
     */
    public function getAvailableMailTemplates($contexts = array())
    {
        //DEPRECATED ?!
        return $this->surroundings->getMailTemplates($contexts);
    }

    /**
     * Get a single mail template.
     *
     * @param int $id
     * @return \ilMailTemplate
     */
    public function getMailTemplate($id)
    {
        return $this->surroundings->getMailTemplate($id);
    }


    /**
     * Get all RoleMappings for this object
     *
     * @return RoleMapping\RoleMapping[]
     */
    public function getRoleMappings()
    {
        return $this->object->getRoleMappings();
    }

    public function getPossibleRoleMappings()
    {
        return $this->object->getPossibleRoleMappings();
    }

    /**
     * Get a specific RoleMapping
     *
     * @return RoleMapping\RoleMapping
     */
    public function getSingleRoleMapping($id)
    {
        assert('is_int($id)');
        return $this->mappings_db->selectFor($id);
    }

    /**
     * Update a specific RoleMapping
     *
     * @param RoleMapping\RoleMapping
     * @return void
     */
    public function updateSingleRoleMapping(RoleMapping\RoleMapping $mapping)
    {
        $this->mappings_db->upsert($mapping);
        return;
    }

    public function createNewMapping(
        int $mapping_id,
        int $role_id,
        int $template_id,
        array $attachments
    ) {
        $mapping = $this->mappings_db->getMappingObject(
            $mapping_id,
            (int) $this->getObject()->getId(),
            $role_id,
            $template_id,
            $attachments
        );
        $this->mappings_db->upsert($mapping);
    }

    /**
     * Delete all mappings at the current object.
     *
     * @return void
     */
    public function deleteAllMappings()
    {
        $obj_id = (int) $this->getObject()->getId();
        $this->mappings_db->deleteForObject($obj_id);
    }

    public function deleteForMappingId(int $id)
    {
        $this->mappings_db->deleteFor($id);
    }

    /**
     * Update the course mailing object
     *
     * @param 	string 		$title
     * @param 	string 		$description
     * @return 	void
     */
    public function updateObject($title, $description)
    {
        assert('is_string($title)');
        assert('is_string($title)');

        $object = $this->getObject();
        $object->setTitle($title);
        $object->setDescription($description);
        $object->update();
    }

    /**
     * @param string[]|null $sort 	array(field, "asc"|"desc")|null
     * @param int[]|null $limit 	array(length, offset)|null
     *
     * @return LogEntry[]
     */
    public function getMailLogsForCourse($sort = null, $limit = null)
    {
        $crs_ref = $this->surroundings->getParentCourseRefId();
        return $this->logs_db->selectForCourse($crs_ref, $sort, $limit);
    }
    /**
     * @return LogEntry[]
     */
    public function getMailLogsCountForCourse()
    {
        $crs_ref = $this->surroundings->getParentCourseRefId();
        return $this->logs_db->selectCountForCourse($crs_ref);
    }

    /**
     * Use Ente to get all occasions
     *
     * @return MailingOccasion[]
     */
    public function getMailingOccasionsAtCourse()
    {
        $crs_ref = $this->surroundings->getParentCourseRefId();
        $mailhandler = new CourseMailHandler($crs_ref);
        return $mailhandler->getMailingOccasions();
    }

    /**
     * Use Ente to get all occasions
     *
     * @return MailingOccasion[]
     */
    public function getInvitationDates()
    {
        $crs_ref = $this->surroundings->getParentCourseRefId();
        return $this->getObject()->getInvitationDates($crs_ref);
    }



    /**
     * Get data of a single mail template.
     *
     * @param string $ident
     * @return array<string, string>
     */
    public function getMailTemplateDataByIdent($ident)
    {
        return $this->surroundings->getMailTemplateDataByIdent($ident);
    }

    /**
     * Get all members of parent course
     *
     * @return ilObjUser[]
     */
    public function getMembersOfParentCourse()
    {
        return $this->surroundings->getMembersOfParentCourse();
    }

    /**
     * Send mails with defined template to users given by their ids.
     *
     * @param string 	$template_ident
     * @param int[] 	$usr_ids
     * @param string[]|false 	$attachments
     * @return void
     */
    public function sendManualMailsForUsers($template_ident, array $usr_ids, $attachments = false)
    {
        assert('is_string($template_ident)');
        $occasions = $this->getMailingOccasionsAtCourse();
        $occasion = null;
        foreach ($occasions as $mail_occasion) {
            if ($mail_occasion->templateIdent() == $template_ident) {
                $occasion = $mail_occasion;
            }
        }
        if ($occasion) {
            $mails = array();
            $event = 'manual';
            $params = array(
                'crs_ref_id' => $this->surroundings->getParentCourseRefId(),
                'usr_id' => null,
                'attachments' => $attachments
            );
            foreach ($usr_ids as $usr_id) {
                $params['usr_id'] = $usr_id;
                $mails = array_merge($mails, $occasion->getMails($event, $params));
            }
            $this->clerk->process($mails, $event);
        }
    }

    /**
     * Send mails with defined template based on the config of an object.
     *
     * @param string 	$template_ident
     * @param int 		$obj_ref_id
     * @return void
     */
    public function sendManualMailsForObject($template_ident, $obj_ref_id)
    {
        assert('is_string($template_ident)');
        assert('is_int($obj_ref_id)');

        $occasions = $this->getMailingOccasionsAtCourse();
        $occasion = null;
        foreach ($occasions as $mail_occasion) {
            if ($mail_occasion->templateIdent() == $template_ident) {
                if ((int) $mail_occasion->owner()->getRefId() === $obj_ref_id) {
                    $occasion = $mail_occasion;
                }
            }
        }

        if ($occasion) {
            $mails = array();
            $event = 'manual';

            $obj_param = $occasion->getOwnerParameterName();
            $params = array(
                'crs_ref_id' => $this->surroundings->getParentCourseRefId(),
                $obj_param => $obj_ref_id
            );

            $mails = $occasion->getMails($event, $params);
            $this->clerk->process($mails, $event);
        }
    }


    /**
     * @return Settings/Setting
     */
    public function getSettings()
    {
        return $this->getObject()->getSettings();
    }

    /**
     * @param Settings/Setting $setting
     * @return void
     */
    public function updateSettings($setting)
    {
        $this->settings_db->update($setting);
        $this->getObject()->setSettings($setting);
    }

    /**
     * get all member-ids with a certain role
     *
     * @param int 	$role_id
     * @return int[]
     */
    public function getCourseMemberIdsWithRole($role_id)
    {
        assert('is_int($role_id)');
        return $this->surroundings->getCourseMemberIdsWithRole($role_id);
    }


    /**
     * Get all potential downloadables/export file via ente.
     *
     * @return File[]
     */
    public function getFilesForCourse()
    {
        return $this->object->getFilesForCourse();
    }

    /**
     * @return FileExportInfo[]
     */
    public function getAttachmentOptions()
    {
        return $this->object->buildAttachmentOptions();
    }


    /**
     * Get all log entries for obj id
     *
     * @return LogEntry[]
     */
    public function getLogEntries()
    {
        return $this->settings_db->getLogEntriesFor((int) $this->object->getId());
    }


    /**
     * Update/validate attachment-ids.
     * Check for configured settings and update with
     * current entity-ref_ids and owner ref_ids.
     * Match objects via RBAC-history.
     * If a setting cannot be resolved (=aligned with the new ids),
     * it is dropped entirely.
     *
     * @return void
     */
    public function fixAttachmentIds()
    {
        return $this->getObject()->postfixAttachmentIds();
    }

    /**
     * get the user's role at the course.
     *
     * @param int 	$usr_id
     * @return array
     */
    public function getRolesForMember(int $usr_id)
    {
        return $this->surroundings->getRolesForMember($usr_id);
    }

    public function getRoleIdsForMember(int $usr_id)
    {
        return $this->surroundings->getRoleIdsOfUser($usr_id);
    }
}

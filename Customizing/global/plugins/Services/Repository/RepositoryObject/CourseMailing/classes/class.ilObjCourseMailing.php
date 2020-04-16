<?php
include_once("Services/Repository/classes/class.ilObjectPlugin.php");
require_once("Services/Mail/classes/class.ilFormatMail.php");

use CaT\Plugins\CourseMailing;
use CaT\Plugins\CourseMailing\Surroundings;
use CaT\Plugins\CourseMailing\RoleMapping;
use CaT\Plugins\CourseMailing\Logging;
use CaT\Plugins\CourseMailing\Settings;
use CaT\Ente\ILIAS\ilProviderObjectHelper;

use ILIAS\TMS\Mailing;

/**
 * Object of the plugin
 */
class ilObjCourseMailing extends \ilObjectPlugin implements CourseMailing\ObjCourseMailing
{
    use ilProviderObjectHelper;
    use CourseMailing\DI;

    /**
     * @var CourseMailing\ilActions
     */
    protected $actions = null;

    /**
     * @var RoleMapping\DB
     */
    protected $mappings_db = null;

    /**
     * @var RoleMapping\RoleMapping[]
     */
    protected $role_mappings = null;

    /**
     * @var Settings\DB
     */
    protected $settings_db = null;

    /**
     * @var ILIAS\TMS\Mailing\TMSMailClerk
     */
    protected $clerk = null;

    /**
     * @var Settings\Setting
     */
    protected $settings;

    /**
     * @var ILIAS\TMS\ScheduledEvents\Schedule | null
     */
    protected $schedule;

    /**
     * @inheritdoc
     */
    protected function getDIC()
    {
        return $GLOBALS["DIC"];
    }

    /**
     * Init the type of the plugin. Same value as chosen in plugin.php
     */
    public function initType()
    {
        $this->setType("xcml");
    }

    /**
     * Closure to get txt from plugin
     */
    public function txtClosure()
    {
        return function ($code) {
            return $this->txt($code);
        };
    }

    /**
     * get the directory for this plugin
     * @return string
     */
    public function getPluginDirectory()
    {
        return $this->getPlugin()->getDirectory();
    }

    /**
     * Get actions for repo object
     *
     * @return CourseMailing\ilActions
     */
    public function getActions()
    {
        if ($this->actions === null) {
            $this->actions = new CourseMailing\ilActions(
                $this,
                $this->getSurroundings(),
                $this->getMappingsDB(),
                $this->getSettingsDB(),
                $this->getLoggingDB(),
                $this->getMailClerk()
            );
        }
        return $this->actions;
    }


    /**
     * Get surroundings to access other parts of the system
     *
     * @return CourseMailing\Surroundings\Surroundings
     */
    public function getSurroundings()
    {
        return $this->getDI()["surroundings"];
    }

    /**
     * Get db for settings (mappings)
     *
     * @return CourseMailing\RoleMapping\DB
     */
    protected function getMappingsDB()
    {
        return $this->getDI()["mapping.db"];
    }

    /**
     * Get db for settings (days when to send invitations)
     *
     * @return CourseMailing\Settings\DB
     */
    protected function getSettingsDB()
    {
        if ($this->settings_db === null) {
            global $DIC;
            $this->settings_db = new Settings\ilDB($DIC->database(), $DIC->user());
        }
        return $this->settings_db;
    }
    /**
     * Get the schedule for deferred events
     *
     * @return ILIAS\TMS\ScheduledEvents\Schedule
     */
    protected function getSchedule()
    {
        if ($this->schedule === null) {
            require_once('./Services/TMS/ScheduledEvents/classes/Schedule.php');
            global $DIC;
            $this->schedule = new Schedule($DIC->database());
        }
        return $this->schedule;
    }


    /**
     * Return RoleMappings for this object.
     * If there are no mappings configured in the DB,
     * the standard setting is "no mail"
     *
     * Return a RoleMapping for each local role at the course!
     * Search order is this:
     *   1. Try to identify the role by it's title.
     *   2. If there is no match on the title,
     *      maybe there is for the role_id (the role has been renamed)
     *   3. Finally, if there is no proper assignment possible,
     *      fall back to "no mail" by deleting the entry.
     *      This can be done because the object will always be copied
     *      and never be referenced (it holds logs as well...).
     *
     * @return RoleMapping\RoleMapping[]
     */
    public function getRoleMappings(bool $force_reload = false)
    {
        if (
            is_null($this->role_mappings) ||
            $force_reload
        ) {
            $this->role_mappings = $this->getMappingsDB()->selectForObjectId((int) $this->getId());
        }
        return $this->role_mappings;
    }

    public function getPossibleRoleMappings()
    {
        $cur_role_mapping = $this->getRoleMappings();
        $cnt_cur_role_mapping = count($cur_role_mapping);
        $local_roles = $this->getSurroundings()->getLocalRoles();
        $local_role_ids = array_keys($local_roles);

        $missing_roles_mappings = array_map(
            function ($role_id) {
                return $this->getMappingsDB()->getMappingObject(
                    -1,
                    (int) $this->getId(),
                    $role_id
                );
            },
            array_filter(
                $local_role_ids,
                function ($role_id) use ($cur_role_mapping, $cnt_cur_role_mapping) {
                    if ($cnt_cur_role_mapping == 0) {
                        return true;
                    }
                    foreach ($cur_role_mapping as $mapping) {
                        if ($mapping->getRoleId() == $role_id) {
                            return false;
                        }
                    }
                    return true;
                }
            )
        );

        /** @var RoleMapping\RoleMapping[] $mappings */
        $mappings = array_merge($cur_role_mapping, $missing_roles_mappings);
        foreach ($mappings as $key => $mapping) {
            $mappings[$key] = $mapping->withRoleTitle($local_roles[$mapping->getRoleId()]);
        }

        uasort(
            $mappings,
            function (RoleMapping\RoleMapping $a, RoleMapping\RoleMapping $b) {
                return strcasecmp($a->getRoleTitle(), $b->getRoleTitle());
            }
        );

        return $mappings;
    }


    /**
     * Get (additional) Settings for this.
     * @return Settings\Setting
     */
    public function getSettings()
    {
        return $this->getSettingsDB()->selectForObject((int) $this->getId());
    }

    public function setSettings(Settings\Setting $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Get called if the object get be updated
     * Update additional setting values
     */
    public function doUpdate()
    {
        $this->scheduleMailingEvents(); //make sure that settings are written before.
    }

    /**
     * Get called after object creation to read further information
     */
    public function doRead()
    {
        $this->settings = $this->getSettings();
    }

    /**
     * Get called if the object should be deleted.
     * Delete additional settings
     */
    public function doDelete()
    {
        $this->getActions()->deleteAllMappings();
        $this->settings_db->deleteForObject((int) $this->getId());
        $this->deleteExistingMailingEvents();
    }
    /**
     * Gets called if the object is created
     *
     */
    public function doCreate()
    {
        $this->settings = $this->getSettingsDB()->create((int) $this->getId(), 0, 0, false);
        $this->createUnboundProvider("crs", CaT\Plugins\CourseMailing\UnboundProvider::class, __DIR__ . "/UnboundProvider.php");
    }

    /**
     * Get called if the object get be coppied.
     * Copy additional settings to new object
     *
     * @param ilObjCourseMailing $new_obj
     */
    public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
    {
        /**	@var $db CourseMailing\RoleMapping\DB */
        $db = $this->getMappingsDB();
        $nu_obj_id = (int) $new_obj->getId();
        $mappings = $this->getRoleMappings();
        $local_roles = $this->getSurroundings()->getLocalRoles();
        $n_local_roles = $new_obj->getSurroundings()->getLocalRoles();

        foreach ($mappings as $mapping) {
            $title = $local_roles[$mapping->getRoleId()];
            $n_role_id = array_search($title, $n_local_roles);

            if ($n_role_id === false) {
                continue;
            }

            $db->upsert(
                $db->getMappingObject(
                    -1,
                    $nu_obj_id,
                    $n_role_id,
                    $mapping->getTemplateId(),
                    $mapping->getAttachmentIds()
                )
            );
        }
        $settings = $new_obj->settings
            ->withDaysInvitation($this->settings->getDaysInvitation())
            ->withDaysRemindInvitation($this->settings->getDaysRemindInvitation())
            ->withPreventMailing($this->settings->getPreventMailing())
        ;

        $new_obj->getSettingsDB()->update($settings);
        //align attachment settings with new ids.
        $new_obj->postfixAttachmentIds();
    }

    /**
     * get MailingActions with logging mechs
     *
     * @param ilFormatMail 	$umail
     * @param Mailing\MailingDB 	$db
     * @return Mailing\Actions
     */
    public function getMailingActions(ilFormatMail $umail, Mailing\MailingDB $db)
    {
        $logging_db = $this->getLoggingDB();
        return new CaT\Plugins\CourseMailing\ilCourseMailingActions($umail, $db, $logging_db);
    }

    /**
     * Get db for logging
     *
     * @return Mailing\LoggingDB
     * @return Mailing\LoggingD
     */
    protected function getLoggingDB()
    {
        return $this->getDI()["log.db"];
    }

    /**
     * Get the clerk to send mails
     *
     * @return Mailing\TMSMailClerk
     */
    protected function getMailClerk()
    {
        if ($this->clerk === null) {
            require_once("./Services/TMS/Mailing/classes/ilTMSMailing.php");
            $mailing = new \ilTMSMailing();
            $this->clerk = $mailing->getClerk();
        }
        return $this->clerk;
    }

    /**
     * Calculate point of time for invitation- and reminder-mails,
     * create deferred events.
     *
     * @return void
     */
    public function scheduleMailingEvents()
    {
        $schedule = $this->getSchedule();
        $plug = $this->getPlugin();
        $parent_course_ref = $this->getSurroundings()->getParentCourseRefId();

        $this->deleteExistingMailingEvents();
        $invitation_events = $this->getInvitationDates($parent_course_ref);
        if ($invitation_events !== null) {
            list($due_invite, $due_remind) = $invitation_events;

            $now = new DateTime();
            if ($now < $due_invite) {
                $schedule->create(
                    (int) $this->getRefId(),
                    $due_invite,
                    'Modules/Course',
                    $plug::EVENT_INVITATION,
                    array('crs_ref_id' => $parent_course_ref)
                );
            }

            if ($now < $due_remind) {
                $schedule->create(
                    (int) $this->getRefId(),
                    $due_remind,
                    'Modules/Course',
                    $plug::EVENT_REMINDER,
                    array('crs_ref_id' => $parent_course_ref)
                );
            }
        }
    }

    /**
     * Defer a single user-event.
     *
     * @return void
     */
    public function scheduleMailingEventForUser(string $module, string $event, array $payload, \DateTime $due)
    {
        $schedule = $this->getSchedule();
        $schedule->create(
            (int) $this->getRefId(),
            $due,
            $module,
            $event,
            $payload
        );
    }


    /**
     * Get ScheduledEvents for invitation-mails issued by this object.
     *
     * @return ILIAS\TMS\ScheduledEvents\Event[]
     */
    protected function getExistingMailingEvents()
    {
        $schedule = $this->getSchedule();
        $plug = $this->getPlugin();
        $existing_events = array_merge(
            $schedule->getAllFromIssuer(
                (int) $this->getRefId(),
                'Modules/Course',
                $plug::EVENT_INVITATION
            ),
            $schedule->getAllFromIssuer(
                (int) $this->getRefId(),
                'Modules/Course',
                $plug::EVENT_REMINDER
            )
        );
        return $existing_events;
    }

    /**
     * Delete ScheduledEvents for invitation-mails issued by this object.
     *
     * @return void
     */
    protected function deleteExistingMailingEvents()
    {
        $existing_events = $this->getExistingMailingEvents();
        if (count($existing_events) > 0) {
            $this->getSchedule()->delete($existing_events);
        }
    }

    /**
     * Get dates for invitation/reminder mail.
     *
     * @param int 	$crs_ref_id
     * @return DateTime[]|null
     */
    public function getInvitationDates($crs_ref_id)
    {
        assert('is_int($crs_ref_id)');
        $parent_course = \ilObjectFactory::getInstanceByRefId($crs_ref_id);

        $course_start = $parent_course->getCourseStart();

        if ($course_start === null) {
            return null;
        }

        $offset_invite = new \DateInterval('P' . $this->settings->getDaysInvitation() . 'D');
        $offset_reminder = new \DateInterval('P' . $this->settings->getDaysRemindInvitation() . 'D');

        $course_start = $course_start->get(IL_CAL_DATE);
        $course_start = new DateTime($course_start);

        $due_invite = clone $course_start;
        $due_invite->sub($offset_invite);

        $due_remind = clone $course_start;
        $due_remind->sub($offset_reminder);

        return array($due_invite, $due_remind);
    }

    /**
     * Is the deadline for invitation-mails over?
     * @return bool
     */
    public function hasInvitationDatePassed()
    {
        $parent_course_ref = $this->getSurroundings()->getParentCourseRefId();
        $invitation_events = $this->getInvitationDates($parent_course_ref);
        if ($invitation_events !== null) {
            list($due_invite, $due_remind) = $invitation_events;
            $now = new DateTime();
            if ($now > $due_invite) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the static link to the log tab
     *
     * @return string
     */
    public function getLinkToMailLog()
    {
        require_once("Services/Link/classes/class.ilLink.php");
        global $DIC;
        $access = $DIC->access();

        $link = null;
        if ($access->checkAccess("read", "", $this->getRefId())
            && $access->checkAccess("visible", "", $this->getRefId())
        ) {
            $link = ilLink::_getLink($this->getRefId(), $this->getType(), array("cmd" => "show_logs"));
        }

        return $link;
    }


    public function getLinkToInviteSystem()
    {
        global $DIC;
        $access = $DIC->access();

        $link = null;
        if ($access->checkAccess("read", "", $this->getRefId())
            && $access->checkAccess("view_invites", "", $this->getRefId())
        ) {
            $link = $this->getDI()["invites.gui.static.link"];
        }

        return $link;
    }

    /**
     * Get the static link to the log tab
     *
     * @return string
     */
    public function getLinkToMailMembers()
    {
        require_once("Services/Link/classes/class.ilLink.php");
        global $DIC;
        $access = $DIC->access();

        $link = null;
        if ($access->checkAccess("read", "", $this->getRefId())
            && $access->checkAccess("visible", "", $this->getRefId())
        ) {
            $link = ilLink::_getLink($this->getRefId(), $this->getType(), array("cmd" => "show_mail"));
        }

        return $link;
    }

    /**
     * Will be called after course creation with configuration options.
     *
     * @param	mixed	$config
     * @return	void
     */
    public function afterCourseCreation($config)
    {
        foreach ($config as $key => $value) {
            if ($key == CourseMailing\CourseCreation\MailingStep::F_PREVENT_MAILING) {
                $db = $this->getSettingsDB();
                $this->settings = $this->settings
                    ->withPreventMailing((bool) $value);
                $db->update($this->settings);
                $this->update();
            }
        }
        //align attachment settings with new ids.
        $this->postfixAttachmentIds();
    }

    /**
     * Get all potential downloadables/export file via ente.
     *
     * @return File[]
     */
    public function getFilesForCourse()
    {
        $components = $this->getSurroundings()->getFilesForCourse();
        return $components;
    }


    /**
     * @return FileExportInfo[]
     */
    public function buildAttachmentOptions()
    {
        $options = [];
        foreach ($this->getFilesForCourse() as $ente_component) {
            $options[$ente_component->getId()] = sprintf(
                '%s (%s)',
                //$ente_component->getName(),
                $ente_component->getOwner()->getTitle(),
                $ente_component->getType()
            );
        }

        $parent = $this->getParentCourse();
        if (!is_null($parent)) {
            $key = $parent->getId() . ":" . $this->getRefId() . ":ical";
            $options[$key] = $this->plugin->txt("ical_option");
        }

        return $options;
    }

    /**
     * When a course (mailing-object) is copied, the attachment-ids still point to the former course.
     * Re-align that.
     * IDs read entityId: owner's refId (e.g.memberlist), string-id (e.g. signaturelist_export)
     */
    public function postfixAttachmentIds()
    {
        //valid options for this object
        $valid_options = array_keys($this->buildAttachmentOptions());

        //from the valid options, get their history
        $history = []; //old_refid => [$entity_id, $new_ref_id]
        foreach ($valid_options as $valid) {
            list($entity_id, $owner_refid, $cmp_id) = explode(':', $valid);
            $entity_id = (int) $entity_id;
            $owner_refid = (int) $owner_refid;
            $hist_owner_ref = $this->getCopyHistoryOfRef($owner_refid);
            if (!is_null($hist_owner_ref)) {
                $history[$hist_owner_ref] = [$entity_id, $owner_refid];
            }
        }

        $mappings = $this->getRoleMappings(true);//current settings
        foreach ($mappings as $mapping) {
            $current_atachments = $mapping->getAttachmentIds();
            $update = [];
            foreach ($current_atachments as $attachment) {
                if (in_array($attachment, $valid_options)) {
                    //all good, stored setting is valid option
                    $update[] = $attachment;
                } else {
                    //this is the invalid, but current setting:
                    list($crs_id, $owner_refid, $cmp_id) = explode(':', $attachment);
                    $owner_refid = (int) $owner_refid;
                    //if there is a new object in the course
                    //that has a history matching the currently configured object,
                    //update the setting
                    if (array_key_exists($owner_refid, $history)) {
                        list($new_entity_id, $new_owner_ref_id) = $history[$owner_refid];
                        $new_id = implode(':', [
                            $new_entity_id,
                            $new_owner_ref_id,
                            $cmp_id
                        ]);

                        $update[] = $new_id;
                    } else {
                        $update[] = $attachment; //keep erroneous setting (for now)
                    }
                }
            }
            $new_mapping = $mapping->withAttachmentIds($update);
            $this->getActions()->updateSingleRoleMapping($new_mapping);
        }
    }

    /**
     * Get copy-log from RBAC.
     *
     * @param int 	$ref
     * @return null | int
     */
    protected function getCopyHistoryOfRef($ref)
    {
        assert('is_int($ref)');

        global $DIC;
        $db = $DIC["ilDB"];

        $obj_id = ilObject::_lookupObjId($ref);
        $query = "SELECT source_id FROM copy_mappings WHERE obj_id = " . $obj_id;
        $res = $db->query($query);

        if ($db->numRows($res) == 0) {
            return null;
        }

        $row = $db->fetchAssoc($res);
        return array_shift(\ilObject::_getAllReferences($row["source_id"]));
    }

    /**
     * Get the parent course
     *
     * @return ilObjCourse | null
     */
    public function getParentCourse()
    {
        $ref_id = $this->getRefId();
        if (is_null($ref_id)) {
            $ref_ids = ilObject::_getAllReferences($this->getId());
            $ref_id = array_shift($ref_ids);
        }

        global $DIC;
        $tree = $DIC->repositoryTree();
        $crs_obj = null;
        foreach ($tree->getPathFull($ref_id) as $hop) {
            if ($hop['type'] === 'crs') {
                require_once("Services/Object/classes/class.ilObjectFactory.php");
                $crs_obj = ilObjectFactory::getInstanceByRefId($hop["ref_id"]);
                break;
            }
        }
        return $crs_obj;
    }

    public function getInviteDB() : CaT\Plugins\CourseMailing\Invites\DB
    {
        return $this->getDI()['invites.db'];
    }

    protected function getDI()
    {
        return $this->getObjectDIC($this, $this->getDIC());
    }
}

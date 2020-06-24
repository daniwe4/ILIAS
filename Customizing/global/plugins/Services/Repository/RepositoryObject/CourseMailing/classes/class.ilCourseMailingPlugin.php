<?php
include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");


use \CaT\Plugins\CourseMailing\AutomaticMails;
use CaT\Plugins\CourseMailing\DI;
use CaT\Plugins\CourseMailing\Invites;

/**
 * Plugin base class. Keeps all information the plugin needs
 */
class ilCourseMailingPlugin extends ilRepositoryObjectPlugin
{
    use DI;

    const COPY_OPERATION_ID = 58;

    const EVENT_INVITATION = 'mail_invitation';
    const EVENT_REMINDER = 'mail_invitation_reminder';
    const EVENT_INVITATION_SINGLE = 'mail_invitation_single';

    const EVENT_FREETEXT = 'freetext';

    /**
     * Get the name of the Plugin
     *
     * @return string
     */
    public function getPluginName()
    {
        return "CourseMailing";
    }

    /**
     * Defines custom uninstall action like delete table or something else
     */
    protected function uninstallCustom()
    {
    }

    protected function beforeActivation()
    {
        parent::beforeActivation();
        global $DIC;
        $db = $DIC->database();

        $type = $this->getId();

        if (!$this->isRepositoryPlugin($type)) {
            throw new ilPluginException("Object plugin type must start with an x. Current type is " . $type . ".");
        }

        $type_id = $this->getTypeId($type, $db);
        if (!$type_id) {
            $type_id = (int) $this->createTypeId($type, $db);
        }

        $this->assignCopyPermissionToPlugin($type_id, $db);
        $this->createPluginPermissions($type_id, $db);

        return true;
    }

    protected function createPluginPermissions(int $type_id, \ilDBInterface $db)
    {
        include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
        $new_rbac_options = [
            ["read_log", "User is able to read the mail log", "object", 2700],
            ["view_auto_mails", "User is able to view automatic mails", "object", 2800],
            ["mail_to_members", "User is able to send to course members", "object", 2900],
            ["view_invites", "User is able to view invite management", "object", 3000],
            ["edit_invites", "User is able to use invite management", "object", 3100]
        ];

        foreach ($new_rbac_options as $value) {
            if (!$this->permissionExists($value[0], $db)) {
                $new_ops_id = \ilDBUpdateNewObjectType::addCustomRBACOperation($value[0], $value[1], $value[2], $value[3]);
                \ilDBUpdateNewObjectType::addRBACOperation($type_id, $new_ops_id);
            }
        }
    }

    /**
     * Check the permission is already created
     *
     * @param string 	$permission
     * @param \ilDBInterface	$db
     *
     * @return bool
     */
    protected function permissionExists(string $permission, \ilDBInterface $db)
    {
        $query = "SELECT count(ops_id) AS cnt FROM rbac_operations\n"
            . " WHERE operation = " . $db->quote($permission, 'text');

        $res = $db->query($query);
        $row = $db->fetchAssoc($res);

        return $row["cnt"] > 0;
    }

    /**
     * Check current plugin is repository plgind
     *
     * @param string 	$type
     *
     * @return bool
     */
    protected function isRepositoryPlugin($type)
    {
        return substr($type, 0, 1) == "x";
    }

    /**
     * Get id of current type
     *
     * @param string 	$type
     * @param 			$db
     *
     * @return int | null
     */
    protected function getTypeId($type, $db)
    {
        $set = $db->query("SELECT obj_id FROM object_data " .
            " WHERE type = " . $db->quote("typ", "text") .
            " AND title = " . $db->quote($type, "text"));

        if ($db->numRows($set) == 0) {
            return null;
        }

        $rec = $db->fetchAssoc($set);
        return $rec["obj_id"];
    }

    /**
     * Create a new entry in object data
     *
     * @param string 	$type
     * @param 			$db
     *
     * @return int
     */
    protected function createTypeId($type, $db)
    {
        $type_id = $db->nextId("object_data");
        $db->manipulate("INSERT INTO object_data " .
            "(obj_id, type, title, description, owner, create_date, last_update) VALUES (" .
            $db->quote($type_id, "integer") . "," .
            $db->quote("typ", "text") . "," .
            $db->quote($type, "text") . "," .
            $db->quote("Plugin " . $this->getPluginName(), "text") . "," .
            $db->quote(-1, "integer") . "," .
            $db->quote(ilUtil::now(), "timestamp") . "," .
            $db->quote(ilUtil::now(), "timestamp") .
            ")");

        return $type_id;
    }

    /**
     * Assign permission copy to current plugin
     *
     * @param int 		$type_id
     * @param 			$db
     *
     * @return int
     */
    protected function assignCopyPermissionToPlugin($type_id, $db)
    {
        $ops = array(self::COPY_OPERATION_ID);

        foreach ($ops as $op) {
            // check whether type exists in object data, if not, create the type

            if (!$this->permissionIsAssigned($type_id, $op, $db)) {
                $db->manipulate("INSERT INTO rbac_ta " .
                    "(typ_id, ops_id) VALUES (" .
                    $db->quote($type_id, "integer") . "," .
                    $db->quote($op, "integer") .
                    ")");
            }
        }
    }

    /**
     * Checks permission is not assigned to plugin
     *
     * @param int 		$type_id
     * @param int 		$op_id
     * @param 			$db
     *
     * @return bool
     */
    protected function permissionIsAssigned($type_id, $op_id, $db)
    {
        $set = $db->query("SELECT count(typ_id) as cnt FROM rbac_ta " .
                " WHERE typ_id = " . $db->quote($type_id, "integer") .
                " AND ops_id = " . $db->quote($op_id, "integer"));

        $rec = $db->fetchAssoc($set);

        return $rec["cnt"] > 0;
    }

    /**
     * decides if this repository plugin can be copied
     *
     * @return bool
     */
    public function allowCopy()
    {
        return true;
    }

    /**
     * Get a closure to get txts from plugin.
     *
     * @return \Closure
     */
    public function txtClosure()
    {
        return function ($code) {
            return $this->txt($code);
        };
    }

    /**
     * Handle an (mailing) event.
     *
     * @param string	$a_component
     * @param string	$a_event
     * @param mixed		$a_parameter
     *
     * @return void
     */
    public function handleEvent(string $a_component, string $a_event, $a_parameter)
    {
        global $DIC;
        $logger = $DIC['ilLog'];

        //deal with course-update: re-schedule mailing-events
        if ($a_component == 'Modules/Course' && $a_event == 'update') {
            $crs_ref = $a_parameter['object']->getRefId();
            if (!$crs_ref) {
                $refs = \ilObject::_getAllReferences($a_parameter['object']->getId());
                if (!$refs) {
                    return;
                } else {
                    $crs_ref = array_shift($refs);
                }
            }
            $xcml_obj = $this->getFirstCourseMailingBelowCourse($crs_ref);
            if ($xcml_obj) {
                $logger->write('update mailing events on course-change for ref ' . $crs_ref);
                $xcml_obj->scheduleMailingEvents();
            }
            return;
        }

        //regular course mails, i.e.: mails by course-components listening to $a_event
        if ($a_component == 'Modules/Course' && $a_event == 'addParticipant') {
            //$a_parameter has keys obj_id (of course) and usr_id
            $crs_obj = \ilObjectFactory::getInstanceByObjId($a_parameter['obj_id']);
            $refs = \ilObject::_getAllReferences($crs_obj->getId());
            $crs_ref = array_shift(array_keys($refs));

            $xcml_obj = $this->getFirstCourseMailingBelowCourse($crs_ref);
            if ($xcml_obj && $xcml_obj->hasInvitationDatePassed()) {

                //re- schedule
                $usr_id = $a_parameter['usr_id'];
                $now_plus = new DateTime();
                $now_plus->add(new DateInterval("PT1H"));

                $logger->write('delay direct invitation mail for ref ' . $crs_ref . ' and usr ' . $usr_id);
                $xcml_obj->scheduleMailingEventForUser(
                    'Modules/Course',
                    self::EVENT_INVITATION_SINGLE,
                    [
                        'crs_ref_id' => (int) $crs_ref,
                        'usr_id' => (int) $usr_id
                    ],
                    $now_plus
                );
                return;
            }
        }

        if ($a_component == 'Services/Mail' && $a_event == 'mailSend') {
            $context_id = $a_parameter['context_id'];
            require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/CourseMailing/classes/Invites/class.ilCourseMailingInviteContext.php";
            if ($context_id != ilCourseMailingInviteContext::ID) {
                return;
            }

            $recipients = explode(",", $a_parameter['recipients']);
            $message = $a_parameter['message'];
            $subject = $a_parameter['subject'];
            $attachments = $a_parameter['attachments'];
            $context_params = $a_parameter['context_params'];
            $current_user = $DIC['ilUser'];

            $ref_id = $context_params['ref_id'];
            /** @var ilObjCourseMailing $obj */
            $obj = ilObjectFactory::getInstanceByRefId($ref_id);
            $surroundings = $obj->getSurroundings();
            $db = $obj->getInviteDB();

            /** @var \ilTMSMailingLogsDB $log_db */
            $log_db = $this->getDIC()['log.db'];
            foreach ($recipients as $recipient) {
                $recipient = (int)$recipient;
                $name_values = ilObjUser::_lookupName($recipient);

                $db = $db->setInvitedBy($recipient, (int) $obj->getId(), (int) $current_user->getId());
                $mail_options = new \ilMailOptions($recipient);
                $log_db->log(
                    "invite",
                    $this->txt($context_id),
                    join(",", $mail_options->getExternalEmailAddresses()),
                    $name_values["firstname"] . " " . $name_values["lastname"],
                    $recipient,
                    $name_values["login"],
                    $surroundings->getParentCourseRefId(),
                    $subject,
                    $message,
                    $attachments,
                    ''
                );
            }

            return;
        }

        if ($a_component == 'Services/Object' && $a_event == 'beforeDeletion') {
            $obj = $a_parameter["object"];
            if ($obj->getType() == 'role') {
                $mapping_db = $this->getDIC()["mapping.db"];
                $mapping_db->deleteRoleEntriesById((int) $obj->getId());
            }
            return;
        }

        //we cannot do anything if refId was omitted.
        if (array_key_exists('crs_ref_id', $a_parameter)) {
            $logger->write($a_event);

            //check, if the object is still there:
            $cobj = \ilObjectFactory::getInstanceByRefId($a_parameter['crs_ref_id'], false);
            if ($cobj === false) {
                $logger->write('Cannot handle for non-existant object.');
                return;
            }

            $handler = new AutomaticMails\CourseMailHandler((int) $a_parameter['crs_ref_id']);
            $handler->handleEvent($a_component, $a_event, $a_parameter);
        }
    }

    /**
     * Get the first course-mailingbelow crs
     *
     * @param int 	$ref_id
     *
     * @return BookingModalities | null
     */
    protected function getFirstCourseMailingBelowCourse($ref_id)
    {
        global $DIC;
        $tree = $DIC->repositoryTree();
        $objDefinition = $DIC["objDefinition"];
        $childs = $tree->getChilds($ref_id);
        foreach ($childs as $child) {
            $type = $child["type"];
            $child_ref = $child["child"];
            if ($type == "xcml") {
                return \ilObjectFactory::getInstanceByRefId($child_ref);
            }
            if ($objDefinition->isContainer($type)) {
                $mods = $this->getFirstCourseMailingBelowCourse($child["child"]);
                if (!is_null($mods)) {
                    return $mods;
                }
            }
        }
        return null;
    }

    public function rejectUserByHash(string $hash)
    {
        /** @var Invites\DB $invite_db */
        $invite_db = $this->getDIC()['invites.db'];
        $invite_db->rejectByHash($hash);
    }

    public function getRejectLinkFor(int $usr_id, int $obj_id) : string
    {
        /** @var Invites\DB $invite_db */
        $invite_db = $this->getDIC()['invites.db'];
        $hash = $invite_db->createRejectHashFor($usr_id, $obj_id);
        return ILIAS_HTTP_PATH . '/reject.php?client_id=' . CLIENT_ID . '&file=' . $hash;
    }


    protected function getDIC() : Pimple\Container
    {
        global $DIC;
        return $this->getPluginDIC($this, $DIC);
    }
}

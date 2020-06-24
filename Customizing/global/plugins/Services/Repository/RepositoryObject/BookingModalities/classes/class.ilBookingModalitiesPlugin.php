<?php
declare(strict_types=1);
include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");

require_once 'Services/TMS/RepositoryPluginUtilities/HistorizedRepositoryPlugin.php';

use CaT\Plugins\BookingModalities;
use CaT\Plugins\BookingModalities\Reminder\MinMember;

/**
 * Plugin base class. Keeps all information the plugin needs
 */
class ilBookingModalitiesPlugin extends ilRepositoryObjectPlugin implements HistorizedRepositoryPlugin
{
    use BookingModalities\DI;

    const COPY_OPERATION_ID = 58;

    protected static $course_updated = array();

    private static $waitinglist_modus_hierarchy = array(
        "no_waitinglist", //worst
        "without_auto_move_up",
        "with_auto_move_up"//best
    );

    /**
     * Get the name of the Plugin
     *
     * @return string
     */
    public function getPluginName()
    {
        return "BookingModalities";
    }

    /**
     * Defines custom uninstall action like delete table or something else
     */
    protected function uninstallCustom()
    {
    }

    /**
     * @inheritdoc
     */
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
            $type_id = $this->createTypeId($type, $db);
        }

        $this->assignCopyPermissionToPlugin($type_id, $db);
        $this->createPluginPermissions($type_id, $db);

        //on activation, also install global provider
        BookingModalities\UnboundGlobalProvider::createGlobalProvider();

        return true;
    }

    protected function afterDeactivation()
    {
        //on deactivation, also de-install global provider
        BookingModalities\UnboundGlobalProvider::deleteGlobalProvider();
    }

    /**
     * Check current plugin is repository plugin
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
        $set = $db->query("SELECT obj_id FROM object_data\n" .
            " WHERE type = " . $db->quote("typ", "text") . "\n" .
            " AND title = " . $db->quote($type, "text"));

        if ($db->numRows($set) == 0) {
            return null;
        }

        $rec = $db->fetchAssoc($set);
        return (int) $rec["obj_id"];
    }

    /**
     * Create a new entry in object data
     *
     * @param string 	$type
     * @param 			$db
     *
     * @return int
     */
    protected function createTypeId(string $type, \ilDBInterface $db)
    {
        $type_id = $db->nextId("object_data");
        $db->manipulate("INSERT INTO object_data\n" .
            "(obj_id, type, title, description, owner, create_date, last_update) VALUES (\n" .
            $db->quote($type_id, "integer") . ",\n" .
            $db->quote("typ", "text") . ",\n" .
            $db->quote($type, "text") . ",\n" .
            $db->quote("Plugin " . $this->getPluginName(), "text") . ",\n" .
            $db->quote(-1, "integer") . ",\n" .
            $db->quote(ilUtil::now(), "timestamp") . ",\n" .
            $db->quote(ilUtil::now(), "timestamp") .
            ")");

        return $type_id;
    }

    /**
     * Assign permission copy to current plugin
     *
     * @param int 		$type_id
     * @param \ilDBInterface 	$db
     *
     * @return int
     */
    protected function assignCopyPermissionToPlugin(int $type_id, \ilDBInterface $db)
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
     * Creates permissions the plugin needs
     *
     * @param int 		$type_id
     * @param \ilDBInterface	$db
     *
     * @return null
     */
    protected function createPluginPermissions(int $type_id, \ilDBInterface $db)
    {
        include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
        $new_rbac_options = array(array("book_by_this", "Use this for bookings", "object", 2700));

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
     * Checks permission is not assigned to plugin
     *
     * @param int 		$type_id
     * @param int 		$op_id
     * @param \ilDBInterface	$db
     *
     * @return bool
     */
    protected function permissionIsAssigned(int $type_id, int $op_id, \ilDBInterface $db)
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
     * Get the action object
     *
     * @return ilActions
     */
    public function getActions()
    {
        if ($this->actions === null) {
            global $DIC;
            $db = $DIC->database();
            $this->actions = new BookingModalities\ilActions(
                $this,
                $this->getSelectableRolesDB($db),
                $this->getSelectableReasonsDB($db),
                $this->getDownloadableDocumentStorage(),
                $this->getDownloadableDocumentRoleDB($db)
            );
        }

        return $this->actions;
    }

    /**
     * Get db for selectable roles
     *
     * @param \ilDBInterface $db
     *
     * @return BookingModalities\Settings\SelectableRoles\DB
     */
    protected function getSelectableRolesDB(\ilDBInterface $db)
    {
        if ($this->selectable_roles_db === null) {
            $this->selectable_roles_db = new BookingModalities\Settings\SelectableRoles\ilDB($db);
        }

        return $this->selectable_roles_db;
    }

    /**
     * Get db for selectable reasons
     *
     * @param \ilDBInterface $db
     *
     * @return BookingModalities\Settings\SelectableReasons\DB
     */
    protected function getSelectableReasonsDB(\ilDBInterface $db)
    {
        if ($this->selectable_reasons_db === null) {
            $this->selectable_reasons_db = new BookingModalities\Settings\SelectableReasons\ilDB($db);
        }

        return $this->selectable_reasons_db;
    }

    /**
     * Get storage for document storage
     *
     * @return BookingModalities\Settings\DownloadableDocument\FileStorage
     */
    protected function getDownloadableDocumentStorage()
    {
        if ($this->file_storage === null) {
            $this->file_storage = new BookingModalities\Settings\DownloadableDocument\FileStorage();
        }
        return $this->file_storage;
    }

    /**
     * Get storage for document storage
     *
     * @param \ilDBInterface $db
     *
     * @return BookingModalities\Settings\DownloadableDocument\DB
     */
    protected function getDownloadableDocumentRoleDB(\ilDBInterface $db)
    {
        if ($this->file_db === null) {
            $this->file_db = new BookingModalities\Settings\DownloadableDocument\ilDB($db);
        }
        return $this->file_db;
    }


    /**
     * Returns the smallest of $values.
     * Null is smaller than everything.
     *
     * @param int[]
     * @return int | null
     */
    public static function lowestOrNull(array $values)
    {
        if (in_array(null, $values) || count($values) === 0) {
            return null;
        } else {
            return min($values);
        }
    }

    /**
     * Returns the highest of $values.
     * Null is bigger than everything.
     *
     * @param int[]
     * @return int | null
     */
    public static function highestOrNull(array $values)
    {
        if (in_array(null, $values) || count($values) === 0) {
            return null;
        } else {
            return max($values);
        }
    }

    /**
     * Returns the best mode for users.
     *
     * @param string[] $values
     * @param bool $worst
     * @return string
     */
    public static function peakWaitingListMode(array $values, $worst = false)
    {
        $tmp_values = array();
        foreach ($values as $value) {
            $index = array_search($value, static::$waitinglist_modus_hierarchy);
            if ($index === false) {
                $index = 0;
            }
            $tmp_values[] = $index;
        }

        if (count($tmp_values) === 0) {
            $index = 0;
        } else {
            if ($worst) {
                $index = min($tmp_values);
            } else {
                $index = max($tmp_values);
            }
        }

        return static::$waitinglist_modus_hierarchy[$index];
    }

    /**
     * Walk through a list of BookingModalities and return lists of their values
     *
     * @param BookingModalities[]
     * @return array[]
     */
    public static function valuesFromModalities(array $bkms)
    {
        $min_members = array();
        $max_members = array();
        $booking_starts = array();
        $booking_ends = array();
        $waiting_list_modes = array();

        //collect all values
        foreach ($bkms as $bkm) {
            $min_members[] = $bkm->getMember()->getMin();
            $max_members[] = $bkm->getMember()->getMax();
            $booking_starts[] = $bkm->getBooking()->getBeginning();
            $booking_ends[] = $bkm->getBooking()->getDeadline();
            $waiting_list_modes[] = $bkm->getWaitinglist()->getModus();
        }

        return array(
            $min_members, $max_members,
            $booking_starts, $booking_ends,
            $waiting_list_modes
        );
    }

    /**
     * Walk through a list of BookingModalities and return
     * the peaks of values in favour of the user.
     *
     * @param BookingModalities[]
     * @return array<integer, int | string>
     */
    public static function bestValuesForUser(array $bkms)
    {
        list(
            $min_members, $max_members,
            $booking_starts, $booking_ends,
            $waiting_list_modes
        ) = self::valuesFromModalities($bkms);

        $min_member = self::lowestOrNull($min_members);
        $max_member = self::highestOrNull($max_members);
        $booking_start = self::highestOrNull($booking_starts);
        $booking_end = self::lowestOrNull($booking_ends);
        $waiting_list_mode = self::peakWaitingListMode($waiting_list_modes, false);

        return array(
            $min_member, $max_member,
            $booking_start, $booking_end,
            $waiting_list_mode
        );
    }

    /**
     * Walk through a list of BookingModalities and return
     * the peaks of values to the most restrictive settings of a course
     *
     * @param BookingModalities[]
     * @return array<integer, int | string>
     */
    public static function mostRestrictiveValues(array $bkms)
    {
        $values = self::valuesFromModalities($bkms);
        $cleaned = array();
        foreach ($values as $value_collection) {
            $cleaned[] = array_filter($value_collection, function ($value) {
                return ($value !== null);
            });
        }

        list($min_members, $max_members,
            $booking_starts, $booking_ends,
            $waiting_list_modes
        ) = $cleaned;

        if (count($min_members) === 0) {
            $min_member = null;
        } else {
            $min_member = max($min_members);
        }
        if (count($max_members) === 0) {
            $max_member = null;
        } else {
            $max_member = min($max_members);
        }
        if (count($booking_starts) === 0) {
            $booking_start = null;
        } else {
            $booking_start = min($booking_starts);
        }
        if (count($booking_ends) === 0) {
            $booking_end = null;
        } else {
            $booking_end = max($booking_ends);
        }
        $waiting_list_mode = self::peakWaitingListMode($waiting_list_modes, true);

        return array(
            $min_member, $max_member,
            $booking_start, $booking_end,
            $waiting_list_mode
        );
    }

    /**
     * Get needed values from bkm. Just best for user
     *
     * @param ilObjBookingModalities[] 	$bkms
     * @param ilDateTime 	$crs_start_date
     *
     * @return array<integer, int | ilDateTime | string>
     */
    public function getBestBkmValues(array $bkms, ilDateTime $crs_start_date)
    {
        list($min_member, $max_member, $booking_start, $booking_end,
            $waiting_list_mode) = $this->bestValuesForUser($bkms);

        $booking_start_date = clone $crs_start_date;
        $booking_start_date->increment(ilDateTime::DAY, -1 * $booking_start);

        $booking_end_date = clone $crs_start_date;
        $booking_end_date->increment(ilDateTime::DAY, -1 * $booking_end);

        return array(
            $max_member, $booking_start_date, $booking_end_date,
            $waiting_list_mode, $min_member
        );
    }

    /**
     * Get all booking modalities below ref_id
     *
     * @param int 	$ref_id
     *
     * @return ilObjBookingModalities[]
     */
    protected function getAllBookingModalitiesBelow($ref_id)
    {
        global $DIC;
        $objDefinition = $DIC["objDefinition"];
        $tree = $DIC->repositoryTree();

        return array_map(
            function ($node) {
                return \ilObjectFactory::getInstanceByRefId($node);
            },
            $tree->getSubTree($tree->getNodeData($ref_id), false, "xbkm")
        );
    }

    /**
     * Handle an event
     * @param string	$a_component
     * @param string	$a_event
     * @param mixed		$a_parameter
     */
    public function handleEvent($a_component, $a_event, $a_parameter)
    {
        if ($a_component == "Plugin/BookingModalities" && ($a_event === 'update' || $a_event === 'delete')) {
            $parent_course = $a_parameter['parent_course'];

            if (!is_null($parent_course)) {
                $this->alterCourseSettingsByModalities($parent_course);
            }
        }

        if ($a_component == "Modules/Course" && $a_event == "update") {
            $course = $a_parameter["object"];

            // Courses that have no ref_id are not in the tree and thus have
            // no booking modalities.
            if (!\ilObject::_getAllReferences($course->getId())) {
                return;
            }

            if (array_key_exists($course->getId(), static::$course_updated)) {
                unset(static::$course_updated[$course->getId()]);
                return;
            }

            $this->alterCourseSettingsByModalities($course);
        }

        if ($a_component == "Services/AccessControl" &&
            $a_event == "assignUser" &&
            (
                array_key_exists('role_id', $a_parameter) &&
                $this->isValidNumber($a_parameter['role_id']) &&
                $this->isMemberRole($a_parameter['role_id'])
            )
        ) {
            $crs_obj_id = (int) $a_parameter['obj_id'];
            $crs_ref_id = array_shift(\ilObject::_getAllReferences($crs_obj_id));
            $modalities = $this->getAllBookingModalitiesBelow($crs_ref_id);
            if (count($modalities) > 0) {
                $skip = false;
                foreach ($modalities as $xbkm) {
                    if ($xbkm->getBooking()->getSkipDuplicateCheck()) {
                        $skip = true;
                    }
                }

                if (!$skip) {
                    $duplicate_bookings = $this->findBookedWaitingListPlacesForDuplicateCourses($crs_obj_id);
                    $xbkm = array_shift($modalities);

                    foreach ($duplicate_bookings as $key => $duplicate_booking) {
                        $xbkm->cancelWaitingListAfterBookingFor($duplicate_booking, (int) $a_parameter["usr_id"]);
                    }
                }
            }
        }
    }

    protected function findBookedWaitingListPlacesForDuplicateCourses($crs_obj_id) : array
    {
        $db = $this->getDIC()["ilDB"];
        $query = <<<SQL
SELECT oref.ref_id
FROM copy_mappings cpy_map
JOIN object_reference oref
    ON oref.obj_id = cpy_map.obj_id
WHERE cpy_map.source_id = (SELECT source_id FROM copy_mappings WHERE obj_id = $crs_obj_id)
    AND cpy_map.obj_id != $crs_obj_id
    AND oref.deleted IS NULL
SQL;

        $res = $db->query($query);
        $ret = [];
        while ($row = $db->fetchAssoc($res)) {
            $ret[] = (int) $row["ref_id"];
        }
        return $ret;
    }

    private function isValidNumber($role_id)
    {
        return !is_null($role_id) && $role_id > 0;
    }

    private function isMemberRole($role_id)
    {
        if (!ilObjRole::_exists($role_id)) {
            return false;
        }

        return strpos(\ilObject::_lookupTitle($role_id), 'il_crs_member_') === 0;
    }

    public function alterCourseSettingsByModalitiesAfterCourseCreation($course)
    {
        $this->alterCourseSettingsByModalities($course);
    }

    /**
     * Collect modalities below the course and adjust settings
     *
     * @param \ilObjCourse	$crs
     * @return void
     */
    public function alterCourseSettingsByModalities($crs)
    {
        $modalities = $this->getAllBookingModalitiesBelow($crs->getRefId());

        list($min_member, $max_member,
            $booking_start, $booking_end,
            $waiting_list_mode) = $this->mostRestrictiveValues($modalities);

        $crs_start = $crs->getCourseStart();
        if ($crs_start) {
            $booking_start_date = clone $crs_start;
            $booking_end_date = clone $crs_start;
            $booking_start_date->increment(ilDateTime::DAY, -1 * $booking_start);
            $booking_end_date->increment(ilDateTime::DAY, -1 * $booking_end);
            $crs->setSubscriptionStart($booking_start_date->get(IL_CAL_UNIX));
            $crs->setSubscriptionEnd($booking_end_date->get(IL_CAL_UNIX));
        }

        if ($min_member || $max_member) {
            $crs->enableSubscriptionMembershipLimitation(true);
            $crs->setSubscriptionMinMembers($min_member);
            $crs->setSubscriptionMaxMembers($max_member);
        } else {
            $crs->enableSubscriptionMembershipLimitation(false);
            $crs->setSubscriptionMinMembers(null);
            $crs->setSubscriptionMaxMembers(null);
        }

        if ($waiting_list_mode === 'no_waitinglist') {
            $crs->setWaitingListAutoFill(false);
            $crs->enableWaitingList(false);
        } else {
            $crs->enableWaitingList(true);
            if ($waiting_list_mode === 'with_auto_move_up') {
                $crs->setWaitingListAutoFill(true);
            }
            if ($waiting_list_mode === 'without_auto_move_up') {
                $crs->setWaitingListAutoFill(false);
            }
        }

        static::$course_updated[$crs->getId()] = $crs->getId();
        $crs->update();
    }

    /**
     * HistorizedRepositoryPlugin implementations
     */
    public function getObjType() : string
    {
        return 'xbkm';
    }
    public function getEmptyPayload() : array
    {
        return [
            'booking_dl_date' => '0001-01-01'
            ,'storno_dl_date' => '0001-01-01'
            ,'booking_dl' => 0
            ,'storno_dl' => 0
            ,'max_members' => 0
            ,'min_members' => 0
            ,'to_be_acknowledged' => false
        ];
    }
    public function getTree() : \ilTree
    {
        global $DIC;
        return $DIC['tree'];
    }

    public function extractPayloadByPluginObject(\ilObjectPlugin $obj) : array
    {
        assert($obj instanceof ilObjBookingModalities);
        $start_date = $this->getCourseStart($obj->getParentCourse());
        $booking_dl = (int) $obj->getBooking()->getDeadline();
        $storno_dl = (int) $obj->getStorno()->getDeadline();
        $members = $obj->getMember();
        $booking = $obj->getBooking();
        $to_be_acknowledged = $booking->getToBeAcknowledged();
        $max_members = (int) $members->getMax();
        $min_members = (int) $members->getMin();
        return
            ['booking_dl_date' => $this->dateMinusDays($start_date, $booking_dl)
            ,'storno_dl_date' => $this->dateMinusDays($start_date, $storno_dl)
            ,'booking_dl' => $booking_dl
            ,'storno_dl' => $storno_dl
            ,'max_members' => $max_members
            ,'min_members' => $min_members
            ,'to_be_acknowledged' => $to_be_acknowledged];
    }
    public function relevantHistCases() : array
    {
        return ['crs'];
    }


    protected function getCourseStart(\ilObjCourse $crs)
    {
        $crs_start = $crs->getCourseStart();
        if ($crs_start instanceof \ilDate) {
            return $crs_start->get(IL_CAL_DATE);
        }
        return '0001-01-01';
    }
    protected function dateMinusDays(string $start_date, int $days)
    {
        if ($start_date === '0001-01-01') {
            return $start_date;
        }
        return \DateTime::createFromFormat('Y-m-d', $start_date)
                    ->sub(new \DateInterval('P' . $days . 'D'))
                    ->format('Y-m-d');
    }

    /**
     * @return MinMember
     */
    public function getReminderSettings() : MinMember
    {
        return $this->getDIC()["config.minmember.db"]->select();
    }

    protected function getDIC()
    {
        global $DIC;
        return $this->getPluginDI($this, $DIC);
    }

    public function sendMinMemberReminder(
        int $crs_ref_id,
        int $xbkm_ref_id,
        array $admins
    ) {
    }

    public function txtClosure() : \Closure
    {
        return function ($code) {
            return $this->txt($code);
        };
    }
}

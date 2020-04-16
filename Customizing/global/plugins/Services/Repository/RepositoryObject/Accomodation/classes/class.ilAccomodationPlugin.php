<?php

declare(strict_types = 1);

require_once __DIR__ . "/../vendor/autoload.php";

use CaT\Plugins\Accomodation;
use CaT\Plugins\Accomodation\Reservation\Note;

/**
 * Plugin base class. Keeps all information the plugin needs
 */
class ilAccomodationPlugin extends ilRepositoryObjectPlugin implements HistorizedRepositoryPlugin
{
    const COPY_OPERATION_ID = 58;

    /**
     * @var Note\ilDB
     */
    protected $note_db;

    /**
     * Get the name of the Plugin
     *
     * @return string
     */
    public function getPluginName()
    {
        return "Accomodation";
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
            $type_id = $this->createTypeId($type, $db);
        }

        $this->assignCopyPermissionToPlugin($type_id, $db);

        //on activation, also install global provider
        Accomodation\UnboundGlobalProvider::createGlobalProvider();

        return true;
    }

    protected function afterDeactivation()
    {
        //on deactivation, also de-install global provider
        Accomodation\UnboundGlobalProvider::deleteGlobalProvider();
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
            $db->quote("Plugin " . $this->getAccomodation(), "text") . "," .
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
     * Get root logger
     *
     * @return ilLog
     */
    protected function getLogger()
    {
        global $DIC;
        return $DIC->logger()->root();
    }


    /**
     * Get ref id of course where event comes from
     *
     * @param int 	$crs_id
     *
     * @return int
     */
    protected function getCrsRefId($crs_id)
    {
        $ref_ids = \ilObject::_getAllReferences((int) $crs_id);
        return array_shift($ref_ids);
    }

    /**
     * Handle an event
     * @param string	$a_component
     * @param string	$a_event
     * @param mixed		$a_parameter
     */
    public function handleEvent($a_component, $a_event, $a_parameter)
    {
        switch ($a_event) {
            case 'deleteParticipant':
            case 'user_canceled_self_from_course':
            case 'superior_canceled_user_from_course':
            case 'user_canceled_self_from_waiting':
            case 'superior_canceled_user_from_waiting':
                $crs_id = $a_parameter["obj_id"];
                $crs_ref_id = (int) $this->getCrsRefId((int) $crs_id);
                $usr_id = (int) $a_parameter["usr_id"];
                $this->deleteAllUserReservations($crs_ref_id, $usr_id);
                break;

            case 'removeFromWaitingList':
                // removeFromWaitingList is in ilCourseWaitingList;
                // if a user is to be moved from waiting to member, his/her role is "member' at this point.
                // Delete accomodations only, if user is being removed entirely.
                $crs_obj_id = (int) $a_parameter['obj_id'];
                $usr_id = (int) $a_parameter['usr_id'];

                require_once("Modules/Course/classes/class.ilCourseParticipant.php");
                $participant = \ilCourseParticipant::_getInstancebyObjId($crs_obj_id, $usr_id);
                if ($participant->isMember() === false) {
                    $crs_ref_id = (int) $this->getCrsRefId($crs_obj_id);
                    $this->deleteAllUserReservations($crs_ref_id, $usr_id);
                }
        }

        if ($a_component === 'Services/User' && $a_event === 'deleteUser') {
            $usr_id = (int) $a_parameter["usr_id"];
            $this->deleteReservationsForUserGlobally($usr_id);
        }
        if ($a_component === 'Modules/Course' && $a_event === 'delete') {
            $crs_ref = $a_parameter['object']->getRefId();
            if (!$crs_ref) {
                $refs = \ilObject::_getAllReferences($a_parameter['object']->getId());
                if (!$refs) {
                    return;
                } else {
                    $crs_ref = array_shift($refs);
                }
            }
            foreach ($this->getAllChildrenOfByType((int) $crs_ref, 'xoac') as $xoac_obj) {
                $xoac_obj->doDelete();
            }
        }
        //deal with course-update: re-schedule mailing-events
        if ($a_component === 'Modules/Course' && $a_event === 'update') {
            $crs_ref = $a_parameter['object']->getRefId();
            if (!$crs_ref) {
                $refs = \ilObject::_getAllReferences($a_parameter['object']->getId());
                if (!$refs) {
                    return;
                } else {
                    $crs_ref = array_shift($refs);
                }
            }
            $objs = $this->getAllChildrenOfByType((int) $crs_ref, 'xoac');
            foreach ($objs as $xoac_obj) {
                $this->getLogger()->write('update mailing events (Accomodation) on course-change for ref ' . $crs_ref);
                $xoac_obj->scheduleMailingEvents();
            }
        }
    }

    /**
     * Delete the reservation for the user
     *
     * @param int 	$crs_ref_id
     * @param int 	$usr_id
     *
     * @return void
     */
    protected function deleteAllUserReservations($crs_ref_id, $usr_id)
    {
        $this->getLogger()->write('Handling "delete participant" at Accomodations');

        $oacs = $this->getAllChildrenOfByType($crs_ref_id, "xoac");
        foreach ($oacs as $oac) {
            $oac->getActions()->deleteAllUserReservations($usr_id);
            $this->getNoteDB()->delete((int) $oac->getId(), $usr_id);
        }
    }


    protected function getNoteDB() : Note\ilDB
    {
        if (is_null($this->note_db)) {
            global $DIC;
            $this->note_db = new Note\ilDB($DIC["ilDB"]);
        }

        return $this->note_db;
    }

    /**
     * Get all children by type recursive
     *
     * @param int 	$ref_id
     * @param string 	$search_type
     *
     * @return Object 	of search type
     */
    public function getAllChildrenOfByType($ref_id, $search_type)
    {
        global $DIC;
        $g_tree = $DIC->repositoryTree();
        $g_objDefinition = $DIC["objDefinition"];

        $childs = $g_tree->getChilds($ref_id);
        $ret = array();

        foreach ($childs as $child) {
            $type = $child["type"];
            if ($type == $search_type) {
                $ret[] = \ilObjectFactory::getInstanceByRefId($child["child"]);
            }

            if ($g_objDefinition->isContainer($type)) {
                $rec_ret = $this->getAllChildrenOfByType($child["child"], $search_type);
                if (!is_null($rec_ret)) {
                    $ret = array_merge($ret, $rec_ret);
                }
            }
        }

        return $ret;
    }

    /**
     * get the reservations-db
     */
    private function getReservationDB()
    {
        if ($this->reservations_db === null) {
            global $ilDB;
            $this->reservations_db = new Accomodation\Reservation\ilDB($ilDB);
        }
        return $this->reservations_db;
    }


    /**
     * If a user has been removed from ILIAS, delete ALL her reservations.
     */
    private function deleteReservationsForUserGlobally($usr_id)
    {
        $this->getReservationDB()->deleteAllForUser($usr_id);
        $this->getNoteDB()->deleteAllForUser($usr_id);
    }


    /**
     * HistorizedRepositoryPlugin implementations
     */
    public function getObjType() : string
    {
        return 'xoac';
    }
    public function getEmptyPayload() : array
    {
        return [
            'accomodation_date_start' => '0001-01-01',
            'accomodation_date_end' => '0001-01-01',
            'accomodation' => ''
        ];
    }
    public function getTree() : \ilTree
    {
        global $DIC;
        return $DIC->repositoryTree();
    }
    public function extractPayloadByPluginObject(\ilObjectPlugin $obj) : array
    {
        assert('$obj instanceof ilObjAccomodation');
        $on = $obj->getObjOvernights();
        $start = $on->getEffectiveStartDate();
        $start_date = null;
        $end = $on->getEffectiveEndDate();
        $end_date = null;
        if ($start instanceof \DateTime) {
            $start_date = $start->format('Y-m-d');
        }
        if ($end instanceof \DateTime) {
            $end_date = $end->format('Y-m-d');
        }
        $venue = $obj->getVenue();
        $venue_title = null;
        if ($venue) {
            $venue_title = $venue->getName();
        }
        return [
            'accomodation_date_start' => $start_date,
            'accomodation_date_end' => $end_date,
            'accomodation' => $venue_title
        ];
    }
    public function relevantHistCases() : array
    {
        return ['crs'];
    }
}

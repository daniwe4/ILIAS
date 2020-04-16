<?php
include_once("Services/Repository/classes/class.ilObjectPlugin.php");

require_once(__DIR__ . "/UnboundProvider.php");

use CaT\Plugins\BookingModalities;
use CaT\Ente\ILIAS\ilProviderObjectHelper;

use ILIAS\TMS\Booking;

/**
 * Object of the plugin
 */
class ilObjBookingModalities extends \ilObjectPlugin implements BookingModalities\ObjBookingModalities
{
    use ilProviderObjectHelper;

    const COURSE_CREATION_MIN_MEMBERS = 'min';
    const COURSE_CREATION_MAX_MEMBERS = 'max';

    protected function getDIC()
    {
        global $DIC;
        return $DIC;
    }

    /**
     * @return Booking\Actions
     */
    public function getBookingActions()
    {
        require_once("Services/TMS/Booking/classes/class.ilTMSBookingActions.php");
        return new ilTMSBookingActions();
    }

    /**
     * Init the type of the plugin. Same value as choosen in plugin.php
     */
    public function initType()
    {
        $this->setType("xbkm");
    }


    private function getEventHandler()
    {
        return $this->getDIC()["ilAppEventHandler"];
    }

    /**
     * Get called if the object get be updated
     * Update additional setting values
     */
    public function doUpdate()
    {
        $this->getActions()->updateBooking($this->booking);
        $this->getActions()->updateStorno($this->storno);
        $this->getActions()->updateMember($this->member);
        $this->getActions()->updateWaitinglist($this->waitinglist);
        $this->getActions()->raiseUpdateEvent();
    }

    /**
     * Get called after object creation to read further information
     */
    public function doRead()
    {
        $this->setBooking($this->getActions()->selectBooking());
        $this->setStorno($this->getActions()->selectStorno());
        $this->setMember($this->getActions()->selectMember());
        $this->setWaitinglist($this->getActions()->selectWaitinglist());
    }

    public function doCreate()
    {
        $this->createUnboundProvider("crs", CaT\Plugins\BookingModalities\UnboundProvider::class, __DIR__ . "/UnboundProvider.php");
        $this->createUnboundProvider("crs", CaT\Plugins\BookingModalities\SharedUnboundProvider::class, __DIR__ . "/SharedUnboundProvider.php");
        $this->getActions()->createEmptySettings();
    }

    /**
     * Get called if the object should be deleted.
     * Delete additional settings
     */
    public function doDelete()
    {
        $this->getActions()->raiseDeleteEvent();
        $this->deleteUnboundProviders();
        $this->getActions()->delete();
    }

    /**
     * Get called if the object get be coppied.
     * Copy additional settings to new object
     */
    public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
    {
        $b = $this->getBooking();
        $s = $this->getStorno();
        $m = $this->getMember();
        $w = $this->getWaitinglist();

        $booking = $new_obj->getBooking();
        $storno = $new_obj->getStorno();
        $member = $new_obj->getMember();
        $waiting_list = $new_obj->getWaitinglist();

        $booking = $booking->withBeginning($b->getBeginning())
                           ->withDeadline($b->getDeadline())
                           ->withModus($b->getModus())
                           ->withToBeAcknowledged($b->getToBeAcknowledged())
                           ->withApproveRoles($this->copyApproveRoles((int) $new_obj->getId(), $b->getApproveRoles()))
                           ->withSkipDuplicateCheck($b->getSkipDuplicateCheck())
                           ->withHideSuperiorApprove($b->getHideSuperiorApprove());

        $storno = $storno->withDeadline($s->getDeadline())
                         ->withHardDeadline($s->getHardDeadline())
                         ->withModus($s->getModus())
                         ->withApproveRoles($this->copyApproveRoles((int) $new_obj->getId(), $s->getApproveRoles()))
                         ->withReasonType($s->getReasonType())
                         ->withReasonOptional($s->getReasonOptional());

        $member = $member->withMin($m->getMin())
                         ->withMax($m->getMax());

        $waiting_list = $waiting_list->withCancellation($w->getCancellation())
                                     ->withMax($w->getMax())
                                     ->withModus($w->getModus());

        $new_obj->setBooking($booking);
        $new_obj->setStorno($storno);
        $new_obj->setMember($member);
        $new_obj->setWaitinglist($waiting_list);
        $new_obj->update();
        $new_obj->getActions()->raiseCreateEvent();
    }

    /**
     * Copy approve roles
     *
     * @param int 	$new_obj_id
     * @param BookingModalities\Settings\ApproveRole[] 	$approve_roles
     *
     * @return BookingModalities\Settings\ApproveRole[]
     */
    protected function copyApproveRoles($new_obj_id, array $approve_roles)
    {
        $DIC = $this->getDIC();
        $approve_role_db = $this->getApproveRoleDB($DIC->database());
        $ret = array();

        foreach ($approve_roles as $approve_role) {
            $ret[] = $approve_role_db->createApproveRole(
                $new_obj_id,
                $approve_role->getParent(),
                $approve_role->getPosition(),
                $approve_role->getRoleId()
            );
        }

        return $ret;
    }

    /**
     * Get actions for repo object
     *
     * @return BookingModalities\ilObjectActions
     */
    public function getActions()
    {
        if ($this->actions === null) {
            $DIC = $this->getDIC();
            $db = $DIC->database();
            $this->actions = new BookingModalities\ilObjectActions(
                $this,
                $this->getBookingDB($db),
                $this->getStornoDB($db),
                $this->getMemberDB($db),
                $this->getWaitinglistDB($db),
                $this->getApproveRoleDB($db),
                $this->getOverviewDB($db),
                $this->getEventHandler()
            );
        }
        return $this->actions;
    }

    /**
     * Get db for settings
     *
     * @param \ilDBInterface 	$db
     *
     * @return BookingModalities\Settings\Booking\DB
     */
    protected function getBookingDB(\ilDBInterface $db)
    {
        if ($this->booking_db === null) {
            $this->booking_db = new BookingModalities\Settings\Booking\ilDB($db);
        }
        return $this->booking_db;
    }

    /**
     * Get db for settings
     *
     * @param \ilDBInterface 	$db
     *
     * @return BookingModalities\Settings\Storno\DB
     */
    protected function getStornoDB(\ilDBInterface $db)
    {
        if ($this->storno_db === null) {
            $this->storno_db = new BookingModalities\Settings\Storno\ilDB($db);
        }
        return $this->storno_db;
    }

    /**
     * Get db for settings
     *
     * @param \ilDBInterface 	$db
     *
     * @return BookingModalities\Settings\Member\DB
     */
    protected function getMemberDB(\ilDBInterface $db)
    {
        if ($this->member_db === null) {
            $this->member_db = new BookingModalities\Settings\Member\ilDB($db);
        }
        return $this->member_db;
    }

    /**
     * Get db for settings
     *
     * @param \ilDBInterface 	$db
     *
     * @return BookingModalities\Settings\Waitinglist\DB
     */
    protected function getWaitinglistDB(\ilDBInterface $db)
    {
        if ($this->waitinglist_db === null) {
            $this->waitinglist_db = new BookingModalities\Settings\Waitinglist\ilDB($db);
        }
        return $this->waitinglist_db;
    }

    /**
     * Get db for settings
     *
     * @param \ilDBInterface 	$db
     *
     * @return BookingModalities\Settings\ApproveRole\DB
     */
    protected function getApproveRoleDB(\ilDBInterface $db)
    {
        if ($this->approve_role_db === null) {
            $this->approve_role_db = new BookingModalities\Settings\ApproveRole\ilDB($db);
        }
        return $this->approve_role_db;
    }

    /**
     * Get overview db.
     *
     * @param  \ilDBInterface $db
     * @return BookingModalities\Overview\DB
     */
    protected function getOverviewDB(\ilDBInterface $db)
    {
        if ($this->overview_db === null) {
            $this->overview_db = new BookingModalities\Overview\ilDB($db);
        }
        return $this->overview_db;
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
     * Get the directory of this plugin
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->plugin->getDirectory();
    }

    /**
     * Get the booking settings
     *
     * @return BookingModalities\Settings\Booking\Booking
     */
    public function getBooking()
    {
        return $this->booking;
    }

    /**
     * Get the storno settings
     *
     * @return Storno
     */
    public function getStorno()
    {
        return $this->storno;
    }

    /**
     * Get the member settings
     *
     * @return Member
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * Get the waitinglist settings
     *
     * @return Waitinglist
     */
    public function getWaitinglist()
    {
        return $this->waitinglist;
    }

    /**
     * Set the booking settings
     *
     * @param BookingModalities\Settings\Booking\Booking 	$booking
     *
     * @return null
     */
    public function setBooking(BookingModalities\Settings\Booking\Booking $booking)
    {
        $this->booking = $booking;
    }

    /**
     * Set the storno settings
     *
     * @param BookingModalities\Settings\Storno\Storno 	$storno
     *
     * @return null
     */
    public function setStorno(BookingModalities\Settings\Storno\Storno $storno)
    {
        $this->storno = $storno;
    }

    /**
     * Set the member settings
     *
     * @param BookingModalities\Settings\Member\Member 	$member
     *
     * @return null
     */
    public function setMember(BookingModalities\Settings\Member\Member $member)
    {
        $this->member = $member;
    }

    /**
     * Set the waitinglist settings
     *
     * @param BookingModalities\Settings\Waitinglist\Waitinglist 	$waitinglist
     *
     * @return null
     */
    public function setWaitinglist(BookingModalities\Settings\Waitinglist\Waitinglist $waitinglist)
    {
        $this->waitinglist = $waitinglist;
    }

    /**
     * Get parent course
     *
     * @return \ilObjCourse | null
     */
    public function getParentCourse()
    {
        $DIC = $this->getDIC();
        $tree = $DIC->repositoryTree();

        $parents = $tree->getPathFull($this->getRefId());
        $parent = array_filter($parents, function ($p) {
            if ($p["type"] == "crs") {
                return $p;
            }
        });

        if (count($parent) > 0) {
            $parent_crs = array_shift($parent);
            require_once("Services/Object/classes/class.ilObjectFactory.php");
            return ilObjectFactory::getInstanceByRefId($parent_crs["ref_id"]);
        }
        return null;
    }

    /**
     * Get the path to the downloadable document.
     * Return null, in none configured.
     *
     * @param int $usr_id
     * @return string | null
     */
    public function getModalitiesDoc($usr_id)
    {
        return $this->plugin->getActions()->getModalitiesDocForUser($usr_id);
    }

    /**
     * get the directory for this plugin
     * @return string
     */
    public function getPluginDirectory()
    {
        return $this->getPlugin()->getDirectory();
    }

    // for course creation
    /**
     * Will be called after course creation with configuration options.
     *
     * @param	mixed	$config
     * @return	void
     */
    public function afterCourseCreation($config)
    {
        $min = $config[self::COURSE_CREATION_MIN_MEMBERS];
        if ($min == "") {
            $min = null;
        }
        if (! is_null($min)) {
            $min = (int)$min;
        }
        $max = $config[self::COURSE_CREATION_MAX_MEMBERS];
        if ($max == "") {
            $max = null;
        }
        if (! is_null($max)) {
            $max = (int)$max;
        }
        $m = $this->getMember()
            ->withMin($min)
            ->withMax($max);
        $this->getActions()->updateMember($m);

        $parent = $this->getParentCourse();
        if (!is_null($parent)) {
            $this->getPlugin()->alterCourseSettingsByModalitiesAfterCourseCreation($parent);
        }
    }

    public function getApproversPositions() : array
    {
        $DIC = $this->getDIC();
        $approve_role_db = $this->getApproveRoleDB($DIC->database());
        $approving_positions = $approve_role_db->getSortedPositionsAscending((int) $this->getId());
        return $approving_positions;
    }

    /**
     * @var	int|null|bool
     */
    protected $approval_object_ref_id = false;

    /**
     * Get the ref-id of an object that displays requests for approval.
     *
     * @return int|null
     */
    public function getApprovalObjectRefId()
    {
        if (!is_bool($this->approval_object_ref_id)) {
            return $this->approval_object_ref_id;
        }

        $subs = $this->getAllBookingApprovals();
        if (count($subs) > 0) {
            $this->approval_object_ref_id = array_shift($subs);
        } else {
            $this->approval_object_ref_id = null;
        }

        return $this->approval_object_ref_id;
    }

    public function checkApprovalStateFor(int $usr_id, int $crs_id)
    {
        if (!ilPluginAdmin::isPluginActive("xbka")) {
            return true;
        }

        $pl = ilPluginAdmin::getPluginObjectById("xbka");
        return $pl->hasUserOpenRequestOnCourse($usr_id, $crs_id);
    }

    protected function getAllBookingApprovals() : array
    {
        $tree = $this->getDIC()["tree"];
        return $tree->getSubTree($tree->getNodeData(1), false, "xbka");
    }

    public function isSelfBooking()
    {
        require_once(__DIR__ . "/Settings/class.ilBookingModalitiesGUI.php");
        $modus = $this->getBooking()->getModus();
        return $modus === \ilBookingModalitiesGUI::SELF_BOOKING;
    }

    public function isSuperiorBooking()
    {
        require_once(__DIR__ . "/Settings/class.ilBookingModalitiesGUI.php");
        $modus = $this->getBooking()->getModus();
        return $modus === \ilBookingModalitiesGUI::SUPERIOR_BOOKING;
    }

    /**
     * remove user from crs waiting list
     * @param int 	$crs_ref_id
     * @param int | null 	$usr_id 	if only a single user might be removed
     * @return void
     */
    public function cancelWaitinglistFor($crs_ref_id, $usr_id = null)
    {
        global $DIC;
        $event_handler = $DIC["ilAppEventHandler"];

        $crs = ilObjectFactory::getInstanceByRefId($crs_ref_id);
        $crs->initWaitingList();
        $waitinglist = $crs->waiting_list_obj;

        foreach ($waitinglist->getUserIds() as $user_id) {
            if (!is_null($usr_id) &&
                $usr_id != $user_id
            ) {
                continue;
            }

            $waitinglist->removeFromList($user_id);

            $param = [
                "crs_ref_id" => $crs_ref_id,
                "usr_id" => $user_id
            ];
            $event_handler->raise("Modules/Course", ILIAS\TMS\Booking\Actions::EVENT_AUTO_CANCELED_WAITING, $param);
        }
    }

    public function cancelWaitingListAfterBookingFor($crs_ref_id, $user_id)
    {
        global $DIC;
        $event_handler = $DIC["ilAppEventHandler"];

        $crs = ilObjectFactory::getInstanceByRefId($crs_ref_id);
        $crs->initWaitingList();
        $waitinglist = $crs->waiting_list_obj;
        if ($waitinglist->isOnList($user_id)) {
            $waitinglist->removeFromList($user_id);
            $param = [
                "crs_ref_id" => $crs_ref_id,
                "usr_id" => $user_id
            ];
            $event_handler->raise("Modules/Course", ILIAS\TMS\Booking\Actions::EVENT_CANCELED_WAITING_AFTER_BOOKING, $param);
        }
    }
}

<?php

namespace CaT\Plugins\BookingModalities;

class ilObjectActions
{
    /**
     * @var ilObjBookingModalities
     */
    protected $object;

    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var Settings\Booking\DB
     */
    protected $booking_db;

    /**
     * @var Settings\Storno\DB
     */
    protected $storno_db;

    /**
     * @var Settings\Member\DB
     */
    protected $member_db;

    /**
     * @var Settings\Waitinglist\DB
     */
    protected $waitinglist_db;

    /**
     * @var Overview\DB
     */
    protected $overview_db;

    /**
     * @var Settings\ApproveRole\DB
     */
    protected $approve_role_db;

    /**
     * @var \ilAppEventHandler
     */
    protected $app_event_handler;



    public function __construct(
        \ilObjBookingModalities $object,
        Settings\Booking\DB $booking_db,
        Settings\Storno\DB $storno_db,
        Settings\Member\DB $member_db,
        Settings\Waitinglist\DB $waitinglist_db,
        Settings\ApproveRole\DB $approve_role_db,
        Overview\DB $overview_db,
        \ilAppEventHandler $app_event_handler
    ) {
        $this->object = $object;
        $this->obj_id = (int) $object->getId();
        $this->booking_db = $booking_db;
        $this->storno_db = $storno_db;
        $this->member_db = $member_db;
        $this->waitinglist_db = $waitinglist_db;
        $this->approve_role_db = $approve_role_db;
        $this->overview_db = $overview_db;
        $this->app_event_handler = $app_event_handler;
    }

    /**
     * Creates empty settings and set them to created object
     *
     * @return null
     */
    public function createEmptySettings()
    {
        $booking = $this->booking_db->create($this->getObjectId());
        $storno = $this->storno_db->create($this->getObjectId());
        $member = $this->member_db->create($this->getObjectId());
        $waitinglist = $this->waitinglist_db->create($this->getObjectId());

        $this->getObject()->setBooking($booking);
        $this->getObject()->setStorno($storno);
        $this->getObject()->setMember($member);
        $this->getObject()->setWaitinglist($waitinglist);
    }

    /**
     * Creates a approve role object
     *
     * @param string 	$parent
     * @param int 	$position
     * @param int 	$role_id
     *
     * @return Settings\ApproveRole\ApproveRole
     */
    public function createApproveRole(string $parent, int $position, int $role_id)
    {
        return $this->approve_role_db->createApproveRole($this->getObjectId(), $parent, $position, $role_id);
    }

    /**
     * Update booking settings
     *
     * @param \Closure $update_fnc
     *
     * @return null
     */
    public function updateBookingWith(\Closure $update_fnc)
    {
        $this->getObject()->setBooking($update_fnc($this->getObject()->getBooking()));
    }

    /**
     * Update storno settings
     *
     * @param \Closure $update_fnc
     *
     * @return null
     */
    public function updateStornoWith(\Closure $update_fnc)
    {
        $this->getObject()->setStorno($update_fnc($this->getObject()->getStorno()));
    }

    /**
     * Update member settings
     *
     * @param \Closure $update_fnc
     *
     * @return null
     */
    public function updateMemberWith(\Closure $update_fnc)
    {
        $this->getObject()->setMember($update_fnc($this->getObject()->getMember()));
    }

    /**
     * Update waitinglist settings
     *
     * @param \Closure $update_fnc
     *
     * @return null
     */
    public function updateWaitinglistWith(\Closure $update_fnc)
    {
        $this->getObject()->setWaitinglist($update_fnc($this->getObject()->getWaitinglist()));
    }

    /**
     * Update the current object
     *
     * @return null
     */
    public function updateObject()
    {
        $this->getObject()->update();
    }

    /**
     * Update booking setting in db
     *
     * @param Settings\Booking\Booking 	$booking
     *
     * @return null
     */
    public function updateBooking(Settings\Booking\Booking $booking)
    {
        $this->booking_db->update($booking);
        $this->approve_role_db->deleteApproveRoles($booking->getObjId(), "booking");
        $this->approve_role_db->createApproveRoles($booking->getApproveRoles());
    }

    /**
     * Update storno setting in db
     *
     * @param Settings\Storno\Storno 	$storno
     *
     * @return null
     */
    public function updateStorno(Settings\Storno\Storno $storno)
    {
        $this->storno_db->update($storno);
        $this->approve_role_db->deleteApproveRoles($storno->getObjId(), "storno");
        $this->approve_role_db->createApproveRoles($storno->getApproveRoles());
    }

    /**
     * Update member setting in db
     *
     * @param Settings\Member\Member 	$storno
     *
     * @return null
     */
    public function updateMember(Settings\Member\Member $member)
    {
        $this->member_db->update($member);
    }

    /**
     * Update waitinglist setting in db
     *
     * @param Settings\Waitinglist\Waitinglist 		$waitinglist
     *
     * @return null
     */
    public function updateWaitinglist(Settings\Waitinglist\Waitinglist $waitinglist)
    {
        $this->waitinglist_db->update($waitinglist);
    }

    /**
     * Select booking settings
     *
     * @return Settings\Booking\Booking
     */
    public function selectBooking()
    {
        return $this->booking_db->selectFor($this->getObjectId());
    }

    /**
     * Select storno settings
     *
     * @return Settings\Storno\Storno
     */
    public function selectStorno()
    {
        return $this->storno_db->selectFor($this->getObjectId());
    }

    /**
     * Select member settings
     *
     * @return Settings\Member\Member
     */
    public function selectMember()
    {
        return $this->member_db->selectFor($this->getObjectId());
    }

    /**
     * Select waitinglist settings
     *
     * @return Settings\Waitinglist\Waitinglist
     */
    public function selectWaitinglist()
    {
        return $this->waitinglist_db->selectFor($this->getObjectId());
    }

    /**
     * Get bookings without storno.
     *
     * @param string $order_field
     * @param string $order_direction
     * @param  int $limit
     * @param  int $offset
     * @param array $selected_columns
     * @return Overview/Overview
     */
    public function getBookings($order_field, $order_direction, $limit, $offset, $selected_columns)
    {
        $parent_crs_obj_id = (int) $this->object->getParentCourse()->getId();
        return $this->overview_db->getBookings($parent_crs_obj_id, $order_field, $order_direction, $limit, $offset, $selected_columns);
    }

    /**
     * Get max bookings.
     *
     * @return int
     */
    public function getMaxBookings()
    {
        $parent_crs_obj_id = (int) $this->object->getParentCourse()->getId();
        return $this->overview_db->getMaxBookings($parent_crs_obj_id);
    }

    /**
     * Get cancellations.
     *
     * @param string $order_field
     * @param string $order_direction
     * @param  int $limit
     * @param  int $offset
     * @param array $selected_columns
     * @return Overview/Overview
     */
    public function getCancellations($order_field, $order_direction, $limit, $offset, $selected_columns)
    {
        $parent_crs_obj_id = (int) $this->object->getParentCourse()->getId();
        return $this->overview_db->getCancellations($parent_crs_obj_id, $order_field, $order_direction, $limit, $offset, $selected_columns);
    }

    /**
     * Get max cancellations.
     *
     * @return int
     */
    public function getMaxCancellations()
    {
        $parent_crs_obj_id = (int) $this->object->getParentCourse()->getId();
        return $this->overview_db->getMaxCancellations($parent_crs_obj_id);
    }


    /**
     * Get options for role selections
     *
     * @return string[]
     */
    public function getRoleOptions()
    {
        $roles = $this->approve_role_db->getRoleOptions();
        $ret = array();
        require_once "Modules/OrgUnit/classes/Positions/class.ilOrgUnitPosition.php";
        $positions = \ilOrgUnitPosition::get();
        foreach ($positions as $position) {
            if (in_array($position->getId(), $roles)) {
                $ret[$position->getId()] = $position->getTitle();
            }
        }
        return $ret;
    }

    /**
     * Get the repo object
     *
     * @return ilObjBookingModalities
     */
    public function getObject()
    {
        if ($this->object === null) {
            throw new \LogicException("No object was set");
        }
        return $this->object;
    }

    /**
     * Get the obj id of current object
     *
     * @return int
     */
    protected function getObjectId()
    {
        return $this->obj_id;
    }

    /**
     * Delete all settings for current object
     *
     * @return null
     */
    public function delete()
    {
        $this->booking_db->deleteFor($this->getObjectId());
        $this->storno_db->deleteFor($this->getObjectId());
        $this->member_db->deleteFor($this->getObjectId());
        $this->waitinglist_db->deleteFor($this->getObjectId());
    }

    /**
     * Get the path to the downloadable document.
     * Return null, in none configured.
     *
     * @param int $usr_id
     * @return string | null
     */
    public function getModalitiesDocForUser(int $usr_id)
    {
        return $this->getObject()->getModalitiesDoc($usr_id);
    }

    /**
     * Get app_event_handler
     *
     * @return \ilAppEventHandler
     */
    public function getAppEventHandler()
    {
        return $this->app_event_handler;
    }

    /**
     * Raise update event.
     */
    public function raiseUpdateEvent()
    {
        $parent_crs = $this->getObject()->getParentCourse();
        $xbkm_ref_id = (int) $this->getObject()->getRefId();
        $params = array(
            'xbkm_ref_id' => $xbkm_ref_id,
            'parent_course' => $parent_crs,
            'object' => $this->getObject()
        );
        $this->getAppEventHandler()->raise("Plugin/BookingModalities", "update", $params);
    }

    /**
     * Raise deleted event.
     */
    public function raiseDeleteEvent()
    {
        $params = array(
            'xbkm_ref_id' => (int) $this->getObject()->getRefId(),
            'parent_course' => $this->getObject()->getParentCourse(),
            'object' => $this->getObject()
        );
        $this->getAppEventHandler()->raise("Plugin/BookingModalities", "delete", $params);
    }

    /**
     * Raise create event.
     */
    public function raiseCreateEvent()
    {
        $params = array(
            'xbkm_ref_id' => (int) $this->getObject()->getRefId(),
            'parent_course' => $this->getObject()->getParentCourse(),
            'object' => $this->getObject()
        );
        $this->getAppEventHandler()->raise("Plugin/BookingModalities", "create", $params);
    }
}

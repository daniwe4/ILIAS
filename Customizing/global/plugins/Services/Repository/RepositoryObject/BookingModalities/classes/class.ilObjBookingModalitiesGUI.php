<?php
require_once "./Services/Repository/classes/class.ilObjectPluginGUI.php";
require_once __DIR__ . "/Settings/class.ilBookingModalitiesGUI.php";
require_once __DIR__ . "/Overview/class.ilOverviewGUI.php";

/**
 * Plugin object GUI class. Baseclass for all GUI action in ILIAS
 *
 * @ilCtrl_isCalledBy ilObjBookingModalitiesGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjBookingModalitiesGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjBookingModalitiesGUI: ilBookingModalitiesGUI, ilOverviewGUI, ilExportGUI
 */
class ilObjBookingModalitiesGUI extends ilObjectPluginGUI
{
    /**
     * Property of parent gui
     *
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var \ilAccess
     */
    protected $g_access;

    /**
     * Called after parent constructor. It's possible to define some plugin special values
     */
    protected function afterConstructor()
    {
        global $DIC;
        $this->g_tabs = $DIC->tabs();
        $this->g_access = $DIC->access();
    }

    /**
    * Get type.  Same value as choosen in plugin.php
    */
    final public function getType()
    {
        return "xbkm";
    }

    /**
    * Handles all commmands of this class, centralizes permission checks
    */
    public function performCommand($cmd)
    {
        $next_class = $this->ctrl->getNextClass();
        $this->activateTab($cmd);
        switch ($next_class) {
            case "ilbookingmodalitiesgui":
                $gui = new ilBookingModalitiesGUI($this, $this->object->getActions());
                $this->ctrl->forwardCommand($gui);
                break;
            case "iloverviewgui":
                $gui = new ilOverviewGUI($this, $this->object->getActions());
                $this->ctrl->forwardCommand($gui);
                break;
            default:
                switch ($cmd) {
                    case ilBookingModalitiesGUI::CMD_EDIT_PROPERTIES:
                        $this->redirectBookingModalities($cmd);
                        break;
                    case ilBookingModalitiesGUI::CMD_SHOW_CONTENT:
                        if ($this->g_access->checkAccess("write", "", $this->object->getRefId())) {
                            $this->redirectBookingModalities(ilBookingModalitiesGUI::CMD_EDIT_PROPERTIES);
                        } else {
                            $this->redirectInfoTab();
                        }
                        break;
                    default:
                        throw new Exception(__METHOD__ . " unknown command " . $cmd);
                }
        }
    }

    /**
    * After object has been created -> jump to this command
    */
    public function getAfterCreationCmd()
    {
        return ilBookingModalitiesGUI::CMD_EDIT_PROPERTIES;
    }

    /**
    * Get standard command
    */
    public function getStandardCmd()
    {
        return ilBookingModalitiesGUI::CMD_SHOW_CONTENT;
    }

    /**
     * Redirect via link to course classification gui
     *
     * @param string 	$cmd
     *
     * @return null
     */
    protected function redirectBookingModalities($cmd)
    {
        $link = $this->ctrl->getLinkTargetByClass(
            array("ilObjBookingModalitiesGUI", "ilBookingModalitiesGUI"),
            $cmd,
            "",
            false,
            false
        );
        \ilUtil::redirect($link);
    }

    /**
     * Redirect via link to Info tab
     *
     * @return null
     */
    protected function redirectInfoTab()
    {
        $link = $this->ctrl->getLinkTargetByClass(
            array("ilObjBookingModalitiesGUI", "ilInfoScreenGUI"),
            "showSummary",
            "",
            false,
            false
        );
        \ilUtil::redirect($link);
    }

    /**
     * Set the tabs for the site and activate current
     *
     * @return null
     */
    protected function setTabs()
    {
        $this->addInfoTab();

        $settings = $this->ctrl->getLinkTargetByClass(
            array("ilObjBookingModalitiesGUI", "ilBookingModalitiesGUI"),
            ilBookingModalitiesGUI::CMD_EDIT_PROPERTIES
        );
        if ($this->g_access->checkAccess("write", "", $this->object->getRefId())) {
            $this->g_tabs->addTab(
                ilBookingModalitiesGUI::CMD_EDIT_PROPERTIES,
                $this->txt("tab_settings"),
                $settings
            );
        }

        $bookings = $this->ctrl->getLinkTargetByClass(
            array("ilObjBookingModalitiesGUI", "ilOverviewGUI"),
            ilOverviewGUI::CMD_SHOW_BOOKINGS
        );

        $cancellations = $this->ctrl->getLinkTargetByClass(
            array("ilObjBookingModalitiesGUI", "ilOverviewGUI"),
            ilOverviewGUI::CMD_SHOW_CANCELLATIONS
        );

        if ($this->g_access->checkAccess("write", "", $this->object->getRefId())) {
            $this->g_tabs->addTab(
                ilOverviewGUI::CMD_SHOW_BOOKINGS,
                $this->txt("tab_bookings"),
                $bookings
            );

            $this->g_tabs->addTab(
                ilOverviewGUI::CMD_SHOW_CANCELLATIONS,
                $this->txt("tab_cancellations"),
                $cancellations
            );
        }

        $this->addExportTab();

        $this->addPermissionTab();
    }

    /**
     * activate current tab
     *
     * @param string 	$cmd
     *
     * @return null
     */
    protected function activateTab($cmd)
    {
        $this->g_tabs->activateTab($cmd);
    }

    /**
     * @inheritdoc
     */
    public function afterSave(\ilObject $newObj)
    {
        /**
         * Imo the following does not belong here, but we seem to have no
         * choice due to ilias architecture.
         */
        $newObj->getActions()->raiseCreateEvent();
        parent::afterSave($newObj);
    }

    /**
    * Goto redirection
    */
    public static function _goto($a_target)
    {
        global $DIC;
        $ctrl = $DIC->ctrl();
        $access = $DIC->access();
        $g_user = $DIC->user();

        $t = explode("_", $a_target[0]);
        $class_name = $a_target[1];

        $xbkm_id = (int) $t[0];
        if (count($t) == 2
            && self::startsWith($t[1], "crs")
        ) {
            if ($g_user->getId() == ANONYMOUS_USER_ID) {
                $link = "login.php?target=xbkm_" . $t[0] . "_" . $t[1] . "&cmd=force_login";
                self::redirect($link);
            }

            $crs_ref_id = (int) substr($t[1], 3);
            $usr_id = (int) $g_user->getId();
            $course = ilObjectFactory::getInstanceByRefId($crs_ref_id);
            $xbkm = \ilObjectFactory::getInstanceByRefId($xbkm_id);
            $is_participant = \ilCourseParticipants::_isParticipant($crs_ref_id, $usr_id);
            $is_on_waitinglist = \ilWaitingList::_isOnList($usr_id, $course->getId());

            if ($is_participant) {
                $txt = $xbkm->txtClosure();
                \ilUtil::sendInfo($txt("booking_link_booked_message"), true);
                self::redirect("");
            }

            if ($is_on_waitinglist) {
                $txt = $xbkm->txtClosure();
                \ilUtil::sendInfo($txt("booking_link_booked_waiting_message"), true);
                self::redirect("");
            }

            if (!self::isInBookingPeriod($course, $xbkm)) {
                $txt = $xbkm->txtClosure();
                \ilUtil::sendInfo($txt("booking_link_period_passed"), true);
                self::redirect("");
            }

            if (!self::isBookable($course, $xbkm)) {
                $txt = $xbkm->txtClosure();
                \ilUtil::sendInfo($txt("booking_link_course_overbooked"), true);
                self::redirect("");
            }

            if (!self::needsApproval($xbkm)) {
                $tms_book_gui = "ilTMSSelfBookingGUI";
                if (self::onlyOnWaitingList($course, $xbkm)) {
                    $tms_book_gui = "ilTMSSelfBookWaitingGUI";
                }

                $ctrl->initBaseClass("ilPersonalDesktopGUI");
                $ctrl->setParameterByClass($tms_book_gui, "crs_ref_id", $crs_ref_id);
                $ctrl->setParameterByClass($tms_book_gui, "usr_id", $g_user->getId());
                $link = $ctrl->getLinkTargetByClass(array("ilPersonalDesktopGUI","ilTrainingSearchGUI", $tms_book_gui), "start", "", false, false);
                $ctrl->setParameterByClass($tms_book_gui, "crs_ref_id", null);
                $ctrl->setParameterByClass($tms_book_gui, "usr_id", null);
                if (!substr($link, 0, strlen("ilias.php")) === "ilias.php") {
                    $link = "ilias.php" . $link;
                }
                self::redirect($link);
            } else {
                $tms_book_gui = "ilSelfBookingWithApproveGUI";
                if (self::onlyOnWaitingList($course, $xbkm)) {
                    $tms_book_gui = "ilSelfBookingWaitingWithApproveGUI";
                }

                $ctrl->initBaseClass("ilObjPluginDispatchGUI");
                $ctrl->setParameterByClass($tms_book_gui, "ref_id", $xbkm->getApprovalObjectRefId());
                $ctrl->setParameterByClass($tms_book_gui, "crs_ref_id", $crs_ref_id);
                $ctrl->setParameterByClass($tms_book_gui, "usr_id", $g_user->getId());
                $link = $ctrl->getLinkTargetByClass(["ilObjPluginDispatchGUI","ilObjBookingApprovalsGUI",$tms_book_gui], "start", "", false, false);
                $ctrl->setParameterByClass($tms_book_gui, "ref_id", null);
                $ctrl->setParameterByClass($tms_book_gui, "crs_ref_id", null);
                $ctrl->setParameterByClass($tms_book_gui, "usr_id", null);
                if (!substr($link, 0, strlen("ilias.php")) === "ilias.php") {
                    $link = "ilias.php" . $link;
                }
                self::redirect($link);
            }
        }

        parent::_goto($a_target);
    }

    /**
     * Checks the course is bookable
     *
     * @param ilObjCourse 	$course
     * @param ilObjBookingModalities 	$xbkm
     *
     * @return bool
     */
    protected static function isBookable(ilObjCourse $course, ilObjBookingModalities $xbkm)
    {
        require_once("Services/Membership/classes/class.ilWaitingList.php");
        $crs_waiting_member = \ilWaitingList::lookupListSize($course->getId());
        $max_waiting = $xbkm->getWaitinglist()->getMax();
        $with_waiting_list = $xbkm->getWaitinglist()->getModus() != "no_waitinglist";
        $crs_member = $course->getMembersObject()->getCountMembers();
        $max_member = $xbkm->getMember()->getMax();

        if (is_null($max_member)) {
            return true;
        }

        if ($crs_member < $max_member) {
            return true;
        }

        if ($crs_member >= $max_member && !$with_waiting_list) {
            return false;
        }

        return $crs_member >= $max_member && $with_waiting_list && $crs_waiting_member < $max_waiting;
    }

    /**
     * Check the user is only able to book on waiting list
     *
     * @param ilObjCourse 	$course
     * @param ilObjBookingModalities 	$xbkm
     *
     * @return bool
     */
    protected static function onlyOnWaitingList(ilObjCourse $course, ilObjBookingModalities $xbkm)
    {
        require_once("Services/Membership/classes/class.ilWaitingList.php");
        $crs_waiting_member = \ilWaitingList::lookupListSize($course->getId());
        $max_waiting = $xbkm->getWaitinglist()->getMax();
        $with_waiting_list = $xbkm->getWaitinglist()->getModus() != "no_waitinglist";
        $crs_member = $course->getMembersObject()->getCountMembers();
        $max_member = $xbkm->getMember()->getMax();

        if (is_null($max_member)) {
            return false;
        }

        return ($crs_member >= $max_member) &&
            $with_waiting_list &&
            $crs_waiting_member < $max_waiting
        ;
    }

    /**
     * Check if the booking needs approval by someone.
     *
     * @param ilObjBookingModalities 	$xbkm
     *
     * @return bool
     */
    protected static function needsApproval(ilObjBookingModalities $xbkm)
    {
        return count($xbkm->getApproversPositions()) > 0 && $xbkm->getApprovalObjectRefId() !== null;
    }

    protected static function redirect($link)
    {
        \ilUtil::redirect($link);
    }

    /**
     * Is today in booking period of course
     *
     * @param ilObjCourse 	$course
     * @param ilObjBookingModalities 	$xbkm
     *
     * @return bool
     */
    protected static function isInBookingPeriod(ilObjCourse $course, ilObjBookingModalities $xbkm)
    {
        $crs_start = $course->getCourseStart();

        if ($crs_start == null) {
            return true;
        }

        $booking_start = $xbkm->getBooking()->getBeginning();
        $booking_end = $xbkm->getBooking()->getDeadline();
        $today_string = date("Y-m-d");

        $booking_start_date = clone $crs_start;
        $booking_start_date->increment(\ilDateTime::DAY, -1 * $booking_start);
        $start_string = $booking_start_date->get(IL_CAL_DATE);

        $booking_end_date = clone $crs_start;
        $booking_end_date->increment(\ilDateTime::DAY, -1 * $booking_end);
        $end_string = $booking_end_date->get(IL_CAL_DATE);

        if ($today_string >= $start_string && $today_string <= $end_string) {
            return true;
        }

        return false;
    }

    protected static function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    /**
    * @inheritdoc
    */
    public function addInfoItems($info)
    {
        $course = $this->object->getParentCourse();
        if (!is_null($course)) {
            $info->addSection($this->txt("informations"));
            $link = \ilLink::_getStaticLink($this->object->getRefId(), 'xbkm', true, "_crs" . $course->getRefId());
            $info->addProperty($this->txt("link_to_user_booking"), $link);
        }
    }
}

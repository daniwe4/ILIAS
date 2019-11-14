<?php

use ILIAS\TMS\Booking;
use ILIAS\TMS\Wizard;

require_once("Services/Form/classes/class.ilPropertyFormGUI.php");

/**
 * Displays the TMS booking
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
abstract class ilTMSCancelGUI extends Wizard\Player
{
    use ILIAS\TMS\MyUsersHelper;

    /**
     * @var \ILIAS\DI\Container
     */
    protected $dic;

    /**
     * @var ilTemplate
     */
    protected $g_tpl;

    /**
     * @var ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var ilObjUser
     */
    protected $g_user;

    /**
     * @var	ilLanguage
     */
    protected $g_lng;

    /**
     * @var ilTree
     */
    protected $g_tree;

    /**
     * @var	mixed
     */
    protected $parent_gui;

    /**
     * @var string
     */
    protected $parent_cmd;

    /**
     * @var ilAppEventHandler
     */
    protected $g_event_handler;

    public function __construct($parent_gui, $parent_cmd, $execute_show = true)
    {
        global $DIC;

        $this->dic = $DIC;
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->g_ctrl = $DIC->ctrl();
        $this->g_user = $DIC->user();
        $this->g_lng = $DIC->language();
        $this->g_tree = $DIC->repositoryTree();
        $this->g_event_handler = $DIC['ilAppEventHandler'];
        $this->g_lng->loadLanguageModule('tms');

        $this->parent_gui = $parent_gui;
        $this->parent_cmd = $parent_cmd;

        /**
         * ToDo: Remove this flag.
         * It's realy ugly, but we need it. If we get here by a plugin parent
         * the plugin executes show by him self. So we don't need it here
         */
        $this->execute_show = $execute_show;
    }

    public function executeCommand()
    {
        // TODO: Check if current user may book course for other user here.
        // assert('$this->g_user->getId() === $_GET["usr_id"]');
        assert('is_numeric($_GET["usr_id"])');
        $usr_id = (int) $_GET["usr_id"];

        assert('is_numeric($_GET["crs_ref_id"])');
        $crs_ref_id = (int) $_GET["crs_ref_id"];

        $ilias_bindings = new Booking\ILIASBindings(
            $this->g_ctrl,
            $this,
            $this->parent_gui,
            $this->parent_cmd,
            $this->getTranslations()
        );

        $crs = ilObjectFactory::getInstanceByRefId($crs_ref_id);

        if (!$this->canCancelForUser($usr_id)) {
            $ilias_bindings->redirectToPreviousLocation(array("nope"), false);
        }

        if (!$this->userHasBookingState($crs_ref_id, $usr_id)) {
            $ilias_bindings->redirectToPreviousLocation(array($this->getBookingStateMessage()), false);
        }

        if ($this->hasStartdate($crs) && $this->isCourseAlreadyStarted($crs)) {
            $ilias_bindings->redirectToPreviousLocation(array($this->g_lng->txt("course_has_allready_begun_no_cancel")), false);
        }

        $state_db = new Wizard\SessionStateDB();
        $wizard = new Booking\Wizard(
            $this->dic,
            $this->getComponentClass(),
            (int) $this->g_user->getId(),
            $crs_ref_id,
            $usr_id,
            $this->getOnFinishClosure()
            );
        $player = new Wizard\Player(
            $ilias_bindings,
            $wizard,
            $state_db
            );

        $this->setParameter($crs_ref_id, $usr_id);

        $cmd = $this->g_ctrl->getCmd("start");
        $content = $player->run($cmd, $_POST);
        assert('is_string($content)');
        $this->g_tpl->setContent($content);
        if ($this->execute_show) {
            $this->g_tpl->show();
        }
    }

    protected function hasStartdate(ilObjCourse $crs)
    {
        $crs_start = $crs->getCourseStart();

        if (is_null($crs_start)) {
            return false;
        }

        return true;
    }

    protected function isCourseAlreadyStarted(ilObjCourse $crs) : bool
    {
        $crs_start = $crs->getCourseStart();
        $crs_start = new \DateTimeImmutable($crs_start->get(IL_CAL_DATE, "Y-m-d"));

        $now = new \DateTimeImmutable(date('Y-m-d'));
        if (!$now === $crs_start) {
            return false;
        }

        $crs_start_date_time = $this->getCrsStartDateTime($crs->getRefId());

        if (is_null($crs_start_date_time)) {
            return $now >= $crs_start;
        }

        $now = new \DateTimeImmutable(date('Y-m-d H:i:s'));

        if ($crs_start_date_time < $now) {
            return true;
        }

        return false;
    }

    protected function getCrsStartDateTime(int $crs_ref_id)
    {
        $sessions = $this->getAllChildrenOfByType($crs_ref_id, "sess");

        if (count($sessions) == 0) {
            return null;
        }

        $appointments = [];
        foreach ($sessions as $session) {
            $appointments[] = ilSessionAppointment::_lookupAppointment($session->getId());
        }

        $start = PHP_INT_MAX;
        foreach ($appointments as $appointment) {
            if ($appointment["start"] < $start) {
                $start = $appointment["start"];
            }
        }

        return new \DateTimeImmutable(date('Y-m-d H:i:s', $start));
    }

    /**
     * Execute this when the player is finished.
     *
     * @param int 	$acting_usr_id
     * @param int 	$target_usr_id
     * @param int 	$crs_ref_id
     * @return void
     */
    abstract protected function callOnFinish($acting_usr_id, $target_usr_id, $crs_ref_id);

    /**
     * Wrap callOnFinish to be called from the Wizard.
     *
     * @return callable
     */
    protected function getOnFinishClosure()
    {
        return function ($acting_usr_id, $target_usr_id, $crs_ref_id) {
            return $this->callOnFinish($acting_usr_id, $target_usr_id, $crs_ref_id);
        };
    }

    /**
     * Lookup the course's obj_id.
     * @param int 	$crs_ref_id
     * @return int
     */
    protected function lookupObjId($crs_ref_id)
    {
        assert('is_int($crs_ref_id)');
        $crs_obj_id = (int) \ilObject::_lookupObjId($crs_ref_id);
        return $crs_obj_id;
    }

    /**
     * @inheritdocs
     */
    protected function getTranslations()
    {
        $trans = new \ILIAS\TMS\TranslationsImpl(
            array(
                static::TXT_TITLE => $this->g_lng->txt('canceling'),
                static::TXT_OVERVIEW_DESCRIPTION => $this->g_lng->txt('cancel_overview_description'),
                static::TXT_CONFIRM => $this->g_lng->txt('cancel_confirm'),
                static::TXT_CANCEL => $this->g_lng->txt('cancel'),
                static::TXT_NEXT => $this->g_lng->txt('btn_next'),
                static::TXT_PREVIOUS => $this->g_lng->txt('btn_previous'),
                static::TXT_NO_STEPS_AVAILABLE => $this->g_lng->txt(static::TXT_NO_STEPS_AVAILABLE),
                static::TXT_ABORTED => $this->g_lng->txt('process_aborted')
            )
        );
        return $trans;
    }

    /**
     * Is current user allowed to cancel for
     * Checks the current user is sperior of
     *
     * @param int 	$usr_id
     *
     * @return bool
     */
    protected function canCancelForUser($usr_id)
    {
        if ($this->g_user->getId() == $usr_id) {
            return true;
        }

        $employees = $this->getUsersWhereCurrentCanViewBookings((int) $this->g_user->getId());
        return array_key_exists($usr_id, $employees);
    }

    /**
     * Raises an event with course ids and user id as params.
     * @param string 	$event
     * @param int 	$usr_id
     * @param int 	$crs_ref_id
     * @return void
     */
    protected function fireBookingEvent($event, $usr_id, $crs_ref_id)
    {
        assert('is_string($event)');
        assert('is_int($usr_id)');
        assert('is_int($crs_ref_id)');

        $crs_obj_id = $this->lookupObjId($crs_ref_id);
        $this->g_event_handler->raise(
            'Modules/Course',
            $event,
            array(
                 'crs_ref_id' => $crs_ref_id,
                 'obj_id' => $crs_obj_id,
                 'usr_id' => $usr_id
             )
         );
    }

    public function getAllChildrenOfByType($ref_id, $search_type)
    {
        $ret = array();
        foreach ($this->g_tree->getSubTree($this->g_tree->getNodeData($ref_id), false, $search_type) as $child) {
            $ret[] = \ilObjectFactory::getInstanceByRefId($child);
        }

        return $ret;
    }

    protected function getAccess()
    {
        return $this->g_access;
    }

    protected function userHasBookingState($crs_ref_id, $usr_id)
    {
        return false;
    }

    protected function getBookingStateMessage($crs_ref_id, $usr_id)
    {
        return "";
    }
}

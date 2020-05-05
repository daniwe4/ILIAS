<?php

use CaT\Plugins\BookingModalities;

/**
 * GUI for editing the properties on a booking modalities object
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilBookingModalitiesGUI
{
    const CMD_EDIT_PROPERTIES = "editProperties";
    const CMD_SAVE_PROPERTIES = "saveProperties";
    const CMD_SHOW_CONTENT = "showContent";

    const F_TITLE = "f_title";
    const F_DESCRIPTION = "f_description";

    const F_BOOKING_STARTING = "f_booking_starting";
    const F_BOOKING_DEADLINE = "f_booking_deadline";
    const F_BOOKING_MODUS = "f_booking_modus";
    const F_BOOKING_APPROVE_BY = "f_booking_approve_by";
    const F_BOOKING_SKIP_DUPLICATE_CHECK = "f_booking_skip_duplicate_check";
    const F_BOOKING_HIDE_SUPERIOR_APPROVE = "f_booking_hide_superior_approve";

    const F_STORNO_DEADLINE = "f_storno_deadline";
    const F_STORNO_HARD_DEADLINE = "f_storno_hard_deadline";
    const F_STORNO_MODUS = "f_storno_modus";
    const F_STORNO_APPROVE_BY = "f_storno_approve_by";
    const F_STORNO_REASONS = "f_storno_reasons";
    const F_STORNO_REASON_OPTIONAL = "f_storno_reason_optional";
    const F_TO_BE_ACKNOWLEDGED = "f_to_be_acknowledged";

    const F_MEMBER_MIN = "f_member_min";
    const F_MEMBER_MAX = "f_member_max";

    const F_WAITINGLIST_CANCELLATION = "f_waitinglist_cancellation";
    const F_WAITINGLIST_MAX = "f_waitinglist_max";
    const F_WAITINGLIST_MODUS = "f_waitinglist_modus";

    const NO_CANCEL = "no_storno";
    const SELF_CANCEL = "self_storno";
    const SUPERIOR_CANCEL = "storno_superior";

    const NO_BOOKING = "no_booking";
    const SELF_BOOKING = "self_booking";
    const SUPERIOR_BOOKING = "booking_superior";


    private static $booking_modus_options = array("no_booking" => "no_booking",
        "self_booking" => "self_booking",
        "booking_superior" => "booking_superior"
    );

    private static $storno_modus_options = array("no_storno" => "no_storno",
        "self_storno" => "self_storno",
        "storno_superior" => "storno_superior"
    );

    private static $waitinglist_modus_options = array("no_waitinglist" => "no_waitinglist",
        "without_auto_move_up" => "without_auto_move_up",
        "with_auto_move_up" => "with_auto_move_up"
    );

    private static $storno_reason_options = array("no_reason" => "no_reason",
        "select_from_list" => "select_from_list",
        "user_input" => "user_input",
        "select_from_list_n_input" => "select_from_list_n_input"
    );

    /**
     * @var ilObjCourseClassificationGUI
     */
    protected $parent_gui;

    /**
     * @var ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $g_tpl;

    public function __construct(ilObjBookingModalitiesGUI $parent_gui, BookingModalities\ilObjectActions $actions)
    {
        global $DIC;
        $this->parent_gui = $parent_gui;
        $this->actions = $actions;
        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();

        $this->txt = $actions->getObject()->txtClosure();
    }

    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd(self::CMD_EDIT_PROPERTIES);
        switch ($cmd) {
            case self::CMD_EDIT_PROPERTIES:
                $this->editProperties();
                break;
            case self::CMD_SAVE_PROPERTIES:
                $this->saveProperties();
                break;
            default:
                throw new Exception(__METHOD__ . " unknown command " . $cmd);
        }
    }

    /**
     * Shows the propety form for edit
     *
     * @return null
     */
    protected function editProperties()
    {
        $form = $this->initForm();
        $this->fillForm($form);
        $this->showForm($form);
    }

    /**
     * Saves the properties
     *
     * @return null
     */
    protected function saveProperties()
    {
        $form = $this->initForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showForm($form);
            return;
        }

        $post = $_POST;

        $object = $this->actions->getObject();
        $object->setTitle($post[self::F_TITLE]);
        $object->setDescription($post[self::F_DESCRIPTION]);

        $booking_vals = $this->getBookingVals($post);
        $storno_vals = $this->getStornoVals($post);
        $member_vals = $this->getMemberVals($post);
        $waitinglist_vals = $this->getWaitinglistVals($post);

        $error = false;
        $tpl = new \ilTemplate("tpl.error_message_save.html", true, true, $this->actions->getObject()->getDirectory());
        if ($booking_vals["beginning"] && $booking_vals["deadline"] && ($booking_vals["beginning"] < $booking_vals["deadline"])) {
            $tpl->setCurrentBlock("message");
            $tpl->setVariable("MESSAGE", $this->txt("booking_beginning_smaller_deadline"));
            $tpl->parseCurrentBlock();
            $error = true;
        }

        if ($storno_vals["deadline"] && $storno_vals["hard_deadline"] && ($storno_vals["deadline"] < $storno_vals["hard_deadline"])) {
            $tpl->setCurrentBlock("message");
            $tpl->setVariable("MESSAGE", $this->txt("storno_deadline_smaller_harddeadline"));
            $tpl->parseCurrentBlock();
            $error = true;
        }

        if ($member_vals["min"] && $member_vals["max"] && ($member_vals["min"] > $member_vals["max"])) {
            $tpl->setCurrentBlock("message");
            $tpl->setVariable("MESSAGE", $this->txt("member_min_smaller_max"));
            $tpl->parseCurrentBlock();
            $error = true;
        }

        if ($error) {
            $form->setValuesByPost();
            $this->showForm($form);
            \ilUtil::sendFailure($tpl->get());
            return;
        }

        $this->saveBooking(
            $booking_vals["beginning"],
            $booking_vals["deadline"],
            $booking_vals["modus"],
            $booking_vals["approve_roles"],
            $booking_vals["skip_duplicate_check"],
            $booking_vals["hide_superior_approve"],
            $booking_vals['to_be_acknowledged']
        );

        $this->saveStorno(
            $storno_vals["deadline"],
            $storno_vals["hard_deadline"],
            $storno_vals["modus"],
            $storno_vals["approve_roles"],
            $storno_vals["reason_type"],
            $storno_vals["reason_optional"]
        );

        $this->saveMember(
            $member_vals["min"],
            $member_vals["max"]
        );

        $this->saveWaitinglist(
            $waitinglist_vals["cancellation"],
            $waitinglist_vals["max"],
            $waitinglist_vals["modus"]
        );

        $this->actions->updateObject();

        ilUtil::sendSuccess($this->txt("settings_successful_saved"), true);
        $this->g_ctrl->redirect($this, self::CMD_EDIT_PROPERTIES);
    }

    /**
     * Get values for booking settings from post
     *
     * @param array 	$post
     *
     * @return string[]
     */
    protected function getBookingVals(array $post)
    {
        $vals = array();
        if ($post[self::F_BOOKING_STARTING] == "") {
            $vals["beginning"] = null;
        } else {
            $vals["beginning"] = (int) $post[self::F_BOOKING_STARTING];
        }

        if ($post[self::F_BOOKING_DEADLINE] == "") {
            $vals["deadline"] = null;
        } else {
            $vals["deadline"] = (int) $post[self::F_BOOKING_DEADLINE];
        }

        if ($post[self::F_BOOKING_MODUS] == "") {
            $vals["modus"] = null;
        } else {
            $vals["modus"] = $post[self::F_BOOKING_MODUS];
        }

        $sel_approve_roles = $post[self::F_BOOKING_APPROVE_BY];
        if (count($sel_approve_roles) == 1 && $sel_approve_roles[0] == "") {
            $vals["approve_roles"] = [];
        } else {
            $approve_roles = array();
            $position = 1;
            foreach ($sel_approve_roles as $sel_approve_role_id) {
                if ($sel_approve_role_id != "") {
                    $approve_roles[] = $this->actions->createApproveRole("booking", $position, (int) $sel_approve_role_id);
                    $position++;
                }
            }

            $vals["approve_roles"] = $approve_roles;
        }


        $vals["skip_duplicate_check"] = false;
        if (isset($post[self::F_BOOKING_SKIP_DUPLICATE_CHECK])
            && $post[self::F_BOOKING_SKIP_DUPLICATE_CHECK] == 1
        ) {
            $vals["skip_duplicate_check"] = (bool) $post[self::F_BOOKING_SKIP_DUPLICATE_CHECK];
        }

        $vals["hide_superior_approve"] = false;
        if (isset($post[self::F_BOOKING_HIDE_SUPERIOR_APPROVE])
            && $post[self::F_BOOKING_HIDE_SUPERIOR_APPROVE] == 1
        ) {
            $vals["hide_superior_approve"] = (bool) $post[self::F_BOOKING_HIDE_SUPERIOR_APPROVE];
        }
        if (\ilPluginAdmin::isPluginActive("xack")) {
            $vals['to_be_acknowledged'] = (bool) $post[self::F_TO_BE_ACKNOWLEDGED];
        } else {
            $vals['to_be_acknowledged'] = false;
        }
        return $vals;
    }

    /**
     * Get values for storno settings from post
     *
     * @param array 	$post
     *
     * @return string[]
     */
    protected function getStornoVals(array $post)
    {
        $vals = array();
        if ($post[self::F_STORNO_DEADLINE] == "") {
            $vals["deadline"] = null;
        } else {
            $vals["deadline"] = (int) $post[self::F_STORNO_DEADLINE];
        }

        if ($post[self::F_STORNO_HARD_DEADLINE] == "") {
            $vals["hard_deadline"] = null;
        } else {
            $vals["hard_deadline"] = (int) $post[self::F_STORNO_HARD_DEADLINE];
        }

        if ($post[self::F_STORNO_MODUS] == "") {
            $vals["modus"] = null;
        } else {
            $vals["modus"] = $post[self::F_STORNO_MODUS];
        }

        $sel_approve_roles = $post[self::F_STORNO_APPROVE_BY];
        if (count($sel_approve_roles) == 1 && $sel_approve_roles[0] == "") {
            $vals["approve_roles"] = [];
        } else {
            $approve_roles = array();
            $position = 1;
            foreach ($sel_approve_roles as $sel_approve_role) {
                if ($sel_approve_role != "") {
                    $approve_roles[] = $this->actions->createApproveRole("storno", $position, $sel_approve_role);
                    $position++;
                }
            }
            $vals["approve_roles"] = $approve_roles;
        }

        $vals["reason_type"] = $post[self::F_STORNO_REASONS];

        $vals["reason_optional"] = false;
        if (isset($post[self::F_STORNO_REASON_OPTIONAL]) && $post[self::F_STORNO_REASON_OPTIONAL] == 1) {
            $vals["reason_optional"] = true;
        }

        return $vals;
    }

    /**
     * Get values for member settings from post
     *
     * @param array 	$post
     *
     * @return string[]
     */
    protected function getMemberVals(array $post)
    {
        $vals = array();
        if ($post[self::F_MEMBER_MIN] == "") {
            $vals["min"] = null;
        } else {
            $vals["min"] = (int) $post[self::F_MEMBER_MIN];
        }

        if ($post[self::F_MEMBER_MAX] == "") {
            $vals["max"] = null;
        } else {
            $vals["max"] = (int) $post[self::F_MEMBER_MAX];
        }

        return $vals;
    }

    /**
     * Get values for waitinglist settings from post
     *
     * @param array 	$post
     *
     * @return string[]
     */
    protected function getWaitinglistVals(array $post)
    {
        $vals = array();
        if ($post[self::F_WAITINGLIST_CANCELLATION] == "") {
            $vals["cancellation"] = null;
        } else {
            $vals["cancellation"] = (int) $post[self::F_WAITINGLIST_CANCELLATION];
        }

        if ($post[self::F_WAITINGLIST_MAX] == "") {
            $vals["max"] = null;
        } else {
            $vals["max"] = (int) $post[self::F_WAITINGLIST_MAX];
        }

        if ($post[self::F_WAITINGLIST_MODUS] == "") {
            $vals["modus"] = null;
        } else {
            $vals["modus"] = $post[self::F_WAITINGLIST_MODUS];
        }

        return $vals;
    }

    /**
     * Save booking settings
     *
     * @param int	$beginning
     * @param int	$deadline
     * @param string	$modus
     * @param string[]  $approve_roles
     * @param bool  $skip_duplicate_check
     * @param bool  $hide_superior_approve
     *
     * @return null
     */
    protected function saveBooking(
        $beginning,
        $deadline,
        $modus,
        array $approve_roles,
        $skip_duplicate_check,
        $hide_superior_approve,
        $to_be_acknowledged
    ) {
        $booking = function (BookingModalities\Settings\Booking\Booking $booking) use (
            $beginning,
            $deadline,
            $modus,
            $approve_roles,
            $skip_duplicate_check,
            $hide_superior_approve,
            $to_be_acknowledged) {
            assert('is_int($beginning) || is_null($beginning)');
            assert('is_int($deadline) || is_null($deadline)');
            assert('is_string($modus) || is_null($modus)');

            $booking = $booking->withBeginning($beginning)
                    ->withDeadline($deadline)
                    ->withModus($modus)
                    ->withApproveRoles($approve_roles)
                    ->withSkipDuplicateCheck($skip_duplicate_check)
                    ->withHideSuperiorApprove($hide_superior_approve);
            if (\ilPluginAdmin::isPluginActive("xack")) {
                $booking = $booking->withToBeAcknowledged($to_be_acknowledged);
            }
            return $booking;
        };
        $this->actions->updateBookingWith($booking);
    }

    /**
     * Save storno settings
     *
     * @param int	$deadline
     * @param int	$hard_deadline
     * @param string	$modus
     * @param ApproveRole[] 	$approve_roles
     * @param string 	$reasons_type
     * @param bool 	$reason_optional
     *
     * @return null
     */
    protected function saveStorno($deadline, $hard_deadline, $modus, array $approve_roles, $reasons_type, $reason_optional)
    {
        $storno = function (BookingModalities\Settings\Storno\Storno $storno) use ($deadline, $hard_deadline, $modus, $approve_roles, $reasons_type, $reason_optional) {
            assert('is_int($deadline) || is_null($deadline)');
            assert('is_int($hard_deadline) || is_null($hard_deadline)');
            assert('is_string($modus) || is_null($modus)');
            assert('is_string($reasons_type) || is_null($reasons_type)');
            assert('is_bool($reason_optional)');

            return $storno->withDeadline($deadline)
                    ->withHardDeadline($hard_deadline)
                    ->withModus($modus)
                    ->withApproveRoles($approve_roles)
                    ->withReasonType($reasons_type)
                    ->withReasonOptional($reason_optional);
        };
        $this->actions->updateStornoWith($storno);
    }

    /**
     * Save member settings
     *
     * @param int	$min
     * @param int	$max
     *
     * @return null
     */
    protected function saveMember($min, $max)
    {
        $member = function (BookingModalities\Settings\Member\Member $member) use ($min, $max) {
            assert('is_int($min) || is_null($min)');
            assert('is_int($max) || is_null($max)');

            return $member->withMin($min)
                    ->withMax($max);
        };
        $this->actions->updateMemberWith($member);
    }

    /**
     * Save waitinglist settings
     *
     * @param int	$cancellation
     * @param int	$max
     * @param string	$modus
     *
     * @return null
     */
    protected function saveWaitinglist($cancellation, $max, $modus)
    {
        $waitinglist = function (BookingModalities\Settings\Waitinglist\Waitinglist $waitinglist) use ($cancellation, $max, $modus) {
            assert('is_int($cancellation) || is_null($cancellation)');
            assert('is_int($max) || is_null($max)');
            assert('is_string($modus) || is_null($modus)');

            return $waitinglist->withCancellation($cancellation)
                    ->withMax($max)
                    ->withModus($modus);
        };
        $this->actions->updateWaitinglistWith($waitinglist);
    }

    /**
     * Display the settings form
     *
     * @param \ilPropertyFormGUI 	$form
     *
     * @return null
     */
    protected function showForm(\ilPropertyFormGUI $form)
    {
        $this->g_tpl->setContent($form->getHtml());
    }

    /**
     * Init properties form
     *
     * @return \ilPropertyFormGUI
     */
    protected function initForm()
    {
        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");

        $form = new \ilPropertyFormGUI();
        $form->setFormAction($this->g_ctrl->getFormAction($this));

        $title_section = new ilFormSectionHeaderGUI();
        $title_section->setTitle($this->txt("settings"));
        $form->addItem($title_section);

        $ti = new \ilTextInputGUI($this->txt("title"), self::F_TITLE);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ti = new \ilTextAreaInputGUI($this->txt("description"), self::F_DESCRIPTION);
        $form->addItem($ti);

        $title_section = new ilFormSectionHeaderGUI();
        $title_section->setTitle($this->txt("booking"));
        $form->addItem($title_section);

        $rad_opt = new \ilRadioGroupInputGUI($this->txt("booking_modus"), self::F_BOOKING_MODUS);
        $rad_opt->setInfo($this->txt("booking_modus_info"));
        foreach (static::$booking_modus_options as $key => $value) {
            $option = new ilRadioOption($this->txt($value), $key);
            $rad_opt->addOption($option);
        }
        $form->addItem($rad_opt);

        $ti = new \ilSelectInputGUI($this->txt("booking_approve_roles"), self::F_BOOKING_APPROVE_BY);
        $ti->setInfo($this->txt("booking_approve_roles_info"));
        $ti->setMulti(true, true);
        $options = [];
        $options[null] = $this->txt("please_select");
        foreach ($this->actions->getRoleOptions() as $key => $role) {
            $options[$key] = $this->translateTitle($role);
        }
        $ti->setOptions($options);
        $form->addItem($ti);

        $ti = new \ilNumberInputGUI($this->txt("booking_starting"), self::F_BOOKING_STARTING);
        $ti->setInfo($this->txt("booking_starting_info"));
        $form->addItem($ti);

        $ti = new \ilNumberInputGUI($this->txt("booking_deadline"), self::F_BOOKING_DEADLINE);
        $ti->setInfo($this->txt("booking_deadline_info"));
        $form->addItem($ti);

        $cb = new \ilCheckboxInputGUI($this->txt("booking_skip_duplicate_check"), self::F_BOOKING_SKIP_DUPLICATE_CHECK);
        $cb->setInfo($this->txt("booking_skip_duplicate_check_info"));
        $cb->setValue(1);
        $form->addItem($cb);

        $cb = new \ilCheckboxInputGUI($this->txt("booking_hide_superior_approve"), self::F_BOOKING_HIDE_SUPERIOR_APPROVE);
        $cb->setInfo($this->txt("booking_hide_superior_approve_info"));
        $cb->setValue(1);
        $form->addItem($cb);

        $object = $this->actions->getObject();
        $course = $object->getParentCourse();
        if (!is_null($course)) {
            $ne = new \ilNonEditableValueGUI($this->txt("link_to_user_booking"), false);
            $link = \ilLink::_getStaticLink($object->getRefId(), 'xbkm', true, "_crs" . $course->getRefId());
            $ne->setValue($link);
            $form->addItem($ne);
        }

        $title_section = new ilFormSectionHeaderGUI();
        $title_section->setTitle($this->txt("storno"));
        $form->addItem($title_section);

        $rad_opt = new \ilRadioGroupInputGUI($this->txt("storno_modus"), self::F_STORNO_MODUS);
        $rad_opt->setInfo($this->txt("storno_modus_info"));
        foreach (static::$storno_modus_options as $key => $value) {
            $option = new ilRadioOption($this->txt($value), $key);
            $rad_opt->addOption($option);
        }
        $form->addItem($rad_opt);

        $ti = new \ilSelectInputGUI($this->txt("storno_approve_roles"), self::F_STORNO_APPROVE_BY);
        $ti->setInfo($this->txt("storno_approve_roles_info"));
        $ti->setMulti(true, true);
        $options = [];
        $options[null] = $this->txt("please_select");
        foreach ($this->actions->getRoleOptions() as $key => $role) {
            $options[$key] = $this->translateTitle($role);
        }
        $ti->setOptions($options);
        $form->addItem($ti);

        $ti = new \ilNumberInputGUI($this->txt("storno_deadline"), self::F_STORNO_DEADLINE);
        $ti->setInfo($this->txt("storno_deadline_info"));
        $form->addItem($ti);

        $ti = new \ilNumberInputGUI($this->txt("storno_hard_deadline"), self::F_STORNO_HARD_DEADLINE);
        $ti->setInfo($this->txt("storno_hard_deadline_info"));
        $form->addItem($ti);

        $rad_opt = new \ilRadioGroupInputGUI($this->txt("storno_reason_type"), self::F_STORNO_REASONS);
        $rad_opt->setInfo($this->txt("storno_reason_type_info"));
        foreach (static::$storno_reason_options as $key => $value) {
            $option = new ilRadioOption($this->txt($value), $key);
            $rad_opt->addOption($option);
        }
        $form->addItem($rad_opt);

        $cb = new \ilCheckboxInputGUI($this->txt("storno_reason_optional"), self::F_STORNO_REASON_OPTIONAL);
        $cb->setValue(1);
        $form->addItem($cb);

        $title_section = new ilFormSectionHeaderGUI();
        $title_section->setTitle($this->txt("member"));
        $form->addItem($title_section);

        $ti = new \ilNumberInputGUI($this->txt("member_min"), self::F_MEMBER_MIN);
        $ti->setInfo($this->txt("member_min_info"));
        $form->addItem($ti);

        $ti = new \ilNumberInputGUI($this->txt("member_max"), self::F_MEMBER_MAX);
        $ti->setInfo($this->txt("member_max_info"));
        $form->addItem($ti);

        $title_section = new ilFormSectionHeaderGUI();
        $title_section->setTitle($this->txt("waitinglist"));
        $form->addItem($title_section);

        $rad_opt = new \ilRadioGroupInputGUI($this->txt("waitinglist_modus"), self::F_WAITINGLIST_MODUS);
        $rad_opt->setInfo($this->txt("waitinglist_modus_info"));
        foreach (static::$waitinglist_modus_options as $key => $value) {
            $option = new ilRadioOption($this->txt($value), $key);
            $rad_opt->addOption($option);
        }
        $form->addItem($rad_opt);

        $ti = new \ilNumberInputGUI($this->txt("waitinglist_cancellation"), self::F_WAITINGLIST_CANCELLATION);
        $ti->setInfo($this->txt("waitinglist_cancellation_info"));
        $form->addItem($ti);

        $ti = new \ilNumberInputGUI($this->txt("waitinglist_max"), self::F_WAITINGLIST_MAX);
        $ti->setInfo($this->txt("waitinglist_max_info"));
        $form->addItem($ti);

        $form->addCommandButton(self::CMD_SAVE_PROPERTIES, $this->txt("save"));
        $form->addCommandButton(self::CMD_EDIT_PROPERTIES, $this->txt("cancel"));

        if (\ilPluginAdmin::isPluginActive("xack")) {
            $acknowledgement_section = new ilFormSectionHeaderGUI();
            $acknowledgement_section->setTitle($this->txt("acknowledgement"));
            $form->addItem($acknowledgement_section);
            $cb = new \ilCheckboxInputGUI($this->txt("to_be_acknowledged"), self::F_TO_BE_ACKNOWLEDGED);
            $cb->setInfo($this->txt('to_be_acknowledged_info'));
            $cb->setValue(1);
            $form->addItem($cb);
        }

        return $form;
    }

    protected function fillForm(\ilPropertyFormGUI $form)
    {
        $object = $this->actions->getObject();

        $value[self::F_TITLE] = $object->getTitle();
        $value[self::F_DESCRIPTION] = $object->getDescription();

        $booking = $object->getBooking();
        $value[self::F_BOOKING_STARTING] = $booking->getBeginning();
        $value[self::F_BOOKING_DEADLINE] = $booking->getDeadline();

        $booking_modus = $booking->getModus();
        if ($booking_modus === null) {
            $booking_modus = "no_booking";
        }
        $value[self::F_BOOKING_MODUS] = $booking_modus;

        $approve_roles = $booking->getApproveRoles();
        $approve_roles = array_map(function ($approve_role) {
            return $approve_role->getRoleId();
        }, $approve_roles);
        $value[self::F_BOOKING_APPROVE_BY] = $approve_roles;

        $value[self::F_BOOKING_SKIP_DUPLICATE_CHECK] = $booking->getSkipDuplicateCheck();
        $value[self::F_BOOKING_HIDE_SUPERIOR_APPROVE] = $booking->getHideSuperiorApprove();

        $storno = $object->getStorno();
        $value[self::F_STORNO_DEADLINE] = $storno->getDeadline();
        $value[self::F_STORNO_HARD_DEADLINE] = $storno->getHardDeadline();
        $value[self::F_STORNO_REASON_OPTIONAL] = $storno->getReasonOptional();

        $storno_modus = $storno->getModus();
        if ($storno_modus === null) {
            $storno_modus = "no_storno";
        }
        $value[self::F_STORNO_MODUS] = $storno_modus;

        $approve_roles = $storno->getApproveRoles();
        $approve_roles = array_map(function ($approve_role) {
            return $approve_role->getRoleId();
        }, $approve_roles);
        $value[self::F_STORNO_APPROVE_BY] = $approve_roles;

        $reason_type = "no_reason";
        if ($storno->getReasonType() !== null) {
            $reason_type = $storno->getReasonType();
        }
        $value[self::F_STORNO_REASONS] = $reason_type;

        $member = $object->getMember();
        $value[self::F_MEMBER_MIN] = $member->getMin();
        $value[self::F_MEMBER_MAX] = $member->getMax();

        $waitinglist = $object->getWaitinglist();
        $value[self::F_WAITINGLIST_CANCELLATION] = $waitinglist->getCancellation();
        $value[self::F_WAITINGLIST_MAX] = $waitinglist->getMax();

        $waitinglist_modus = $waitinglist->getModus();
        if ($waitinglist_modus === null) {
            $waitinglist_modus = "no_waitinglist";
        }
        $value[self::F_WAITINGLIST_MODUS] = $waitinglist_modus;
        if (\ilPluginAdmin::isPluginActive("xack")) {
            $value[self::F_TO_BE_ACKNOWLEDGED] = $booking->getToBeAcknowledged();
        }

        $form->setValuesByArray($value);
    }

    /**
     * Translates il_role titles
     *
     * @param string 	$title
     *
     * @return string
     */
    protected function translateTitle($title)
    {
        $length = strlen("il_");
        if (substr($title, 0, $length) === "il_") {
            $title = $this->txt($title);
        }

        return $title;
    }

    /**
     * @param 	string	$code
     * @return	string
     */
    public function txt($code)
    {
        assert('is_string($code)');
        $txt = $this->txt;
        return $txt($code);
    }
}

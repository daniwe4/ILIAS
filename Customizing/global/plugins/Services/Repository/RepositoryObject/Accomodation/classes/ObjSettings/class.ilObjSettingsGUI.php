<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Plugins\Accomodation\ilActions;
use CaT\Plugins\Accomodation\ObjSettings\ObjSettings;
use CaT\Plugins\Accomodation\Reservation\CourseInformationFormGUI;

/**
 * GUI for Settings
 */
class ilObjSettingsGUI
{
    use CourseInformationFormGUI;

    const CMD_EDIT = "editProperties";
    const CMD_SAVE = "saveObjSettings";

    const F_TITLE = "title";
    const F_DESCRIPTION = "description";

    const F_DATE_SOURCE = "f_date_source";
    const F_DATE_FROM_COURSE = "f_date_source_course";
    const F_DATE_FROM_SETTINGS = "f_date_source_settings";
    const F_STARTDATE = "f_startdate";
    const F_ENDDATE = "f_enddate";

    const F_VENUE = "venue";
    const F_VENUE_SOURCE = "venue_source";
    const F_VENUE_FROM_COURSE = "venue_from_course";
    const F_VENUE_FROM_SELECTION = "venue_from_selection";
    const F_PRIOR = "prior_day";
    const F_FOLLOWING = "following_day";
    const F_DEADLINE = "deadline";

    const F_MAILING_SOURCE = "mailing_source";
    const F_SEND_DAYS_BEFORE = "f_days_before";
    const F_RECIPIENT = "f_recipient";
    const F_SEND_REMINDER_DAYS_BEFORE = "f_reminder_days_before";
    const F_MAILING_FROM_VENUE = "mailing_from_venue";
    const F_MAILING_FROM_OBJECT = "mailing_from_object";

    const F_EDIT_NOTES = "edit_notes";

    /**
     * @var \ilCtrl
     */
    protected $ctrl;

    /**
     * @var \ilAccess
     */
    protected $access;

    /**
     * @var \ilGlobalTemplateInterface
     */
    protected $tpl;

    /**
     * @var ilActions
     */
    protected $actions;

    /**
     * @var Closure
     */
    protected $txt;

    public function __construct(ilActions $actions, Closure $txt)
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->access = $DIC->access();
        $this->actions = $actions;
        $this->txt = $txt;
        $this->g_app_event_handler = $DIC["ilAppEventHandler"];
    }

    public function txt(string $code)
    {
        $txt = $this->txt;
        return $txt($code);
    }

    /**
     * Delegate commands
     *
     * @return void
     *@throws Exception
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_EDIT:
                $this->editSettings();
                break;
            case self::CMD_SAVE:
                $this->saveObjSettings();
                break;
            default:
                throw new Exception(__METHOD__ . ": unkown command " . $cmd);
        }
    }

    /**
     * command: show the editing GUI
     */
    protected function editSettings(ilPropertyFormGUI $form = null)
    {
        if ($form === null) {
            $form = $this->initBaseForm();
            $form = $this->initInformationForm($form);
            $form = $this->initSettingsForm($form);

            $form = $this->fillForm($form);
        }

        $form->addCommandButton(self::CMD_SAVE, $this->txt("xoac_save"));
        $form->addCommandButton(self::CMD_EDIT, $this->txt("xoac_cancel"));

        $this->tpl->setContent($form->getHtml());
    }

    protected function saveObjSettings()
    {
        $form = $this->initBaseForm();
        $form = $this->initSettingsForm($form);

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->editSettings($form);
            return;
        }

        $post = $_POST;

        $this->actions->updateObjBasicSettings(
            $post[self::F_TITLE],
            $post[self::F_DESCRIPTION]
        );

        $errors = $this->validatePost($post);
        if (count($errors) > 0) {
            ilUtil::sendFailure(implode('<br>', $errors), true);
            $form->setValuesByPost();
            $this->editSettings($form);
            return;
        }

        $start_date = $post[self::F_STARTDATE];
        if (!is_null($start_date)) {
            $start_date = new DateTime($start_date);
        }

        $end_date = $post[self::F_ENDDATE];
        if (!is_null($end_date)) {
            $end_date = new DateTime($end_date);
        }

        $update_settings = function (ObjSettings $obj_settings) use ($post, $start_date, $end_date) {
            return $obj_settings
                ->withLocationObjId((int) $post[self::F_VENUE])
                ->withDatesByCourse($post[self::F_DATE_SOURCE] === self::F_DATE_FROM_COURSE)
                ->withStartDate($start_date)
                ->withEndDate($end_date)
                ->withLocationFromCourse($post[self::F_VENUE_SOURCE] === self::F_VENUE_FROM_COURSE)
                ->withPriorDayAllowed((bool) $post[self::F_PRIOR])
                ->withFollowingDayAllowed((bool) $post[self::F_FOLLOWING])
                ->withBookingEnd((int) $post[self::F_DEADLINE])
                ->withMailsettingsFromVenue($post[self::F_MAILING_SOURCE] === self::F_MAILING_FROM_VENUE)
                ->withMailRecipient($post[self::F_RECIPIENT])
                ->withSendDaysBefore((int) $post[self::F_SEND_DAYS_BEFORE])
                ->withSendReminderDaysBefore((int) $post[self::F_SEND_REMINDER_DAYS_BEFORE])
                ->withEditNotes((bool) $post[self::F_EDIT_NOTES])
            ;
        };

        $this->actions->updateObjSettings($update_settings);
        $this->actions->updateObject();

        ilUtil::sendSuccess($this->txt("settings_successful_saved"), true);

        $this->ctrl->redirect($this, self::CMD_EDIT);
    }
    /**
     * @param 	array <string,mixed> 	$post
     * @return 	string[]
     */
    protected function validatePost(array $post) : array
    {
        $errors = [];
        list($start, $end) = $this->getDateValuesFromPost($post);
        if ($post[self::F_DATE_SOURCE] === self::F_DATE_FROM_SETTINGS) {
            if (is_null($start) || is_null($end) || $end < $start) {
                $errors[] = $this->txt("end_cant_be_smaller_then_start");
            }
        }

        return $errors;
    }

    /**
     * @param 	array <string,mixed> 	$post
     * @return 	DateTime|null[]
     */
    protected function getDateValuesFromPost(array $post) : array
    {
        $start_date = $post[self::F_STARTDATE];
        if (!is_null($start_date)) {
            $start_date = new DateTime($start_date);
        }

        $end_date = $post[self::F_ENDDATE];
        if (!is_null($end_date)) {
            $end_date = new DateTime($end_date);
        }

        return [$start_date, $end_date];
    }

    /**
     * Init a new settings form
     */
    protected function initBaseForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));

        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->txt('settings_section_basics'));
        $form->addItem($section);

        $ti = new ilTextInputGUI($this->txt("settings_title"), self::F_TITLE);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ta = new ilTextareaInputGUI($this->txt("settings_description"), self::F_DESCRIPTION);
        $form->addItem($ta);

        return $form;
    }


    /**
     * Extended settings form
     */
    protected function initSettingsForm(ilPropertyFormGUI $form) : ilPropertyFormGUI
    {
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->txt('settings_section_xoac_config'));
        $form->addItem($section);

        $date_source = new ilRadioGroupInputGUI($this->txt('settings_date_source'), self::F_DATE_SOURCE);
        $ro_fromcourse = new ilRadioOption($this->txt("settings_date_source_course"), self::F_DATE_FROM_COURSE);
        $di_start = new \ilDateTimeInputGUI($this->txt('settings_date_start'), self::F_STARTDATE);
        $di_start->setRequired(true);
        $di_end = new \ilDateTimeInputGUI($this->txt('settings_date_end'), self::F_ENDDATE);
        $di_end->setRequired(true);
        $ro_fromsettings = new ilRadioOption($this->txt("settings_date_source_settings"), self::F_DATE_FROM_SETTINGS);
        $ro_fromsettings->addSubItem($di_start);
        $ro_fromsettings->addSubItem($di_end);

        $date_source->addOption($ro_fromcourse);
        $date_source->addOption($ro_fromsettings);
        $form->addItem($date_source);

        $vsource = new ilRadioGroupInputGUI($this->txt('settings_venue_source'), self::F_VENUE_SOURCE);
        $msource = new ilRadioGroupInputGUI($this->txt('settings_mail_source'), self::F_MAILING_SOURCE);

        if (ilPluginAdmin::isPluginActive('venues') === true) {
            $ro_fromcourse = new ilRadioOption($this->txt("settings_venue_source_course"), self::F_VENUE_FROM_COURSE);
            $ro_selection = new ilRadioOption($this->txt("settings_venue_source_selection"), self::F_VENUE_FROM_SELECTION);

            $venue_options = $this->actions->getVenueListFromPlugin();
            $base = array(null => $this->txt("settings_venue_empty_option"));
            $venue_options = $base + $venue_options;

            $si = new ilSelectInputGUI($this->txt("settings_venue"), self::F_VENUE);
            $si->setOptions($venue_options);
            $si->setRequired(true);
            $ro_selection->addSubItem($si);

            $vsource->addOption($ro_fromcourse);
            $vsource->addOption($ro_selection);

            $ro_fromcourse = new ilRadioOption($this->txt("settings_mailing_source_venue"), self::F_MAILING_FROM_VENUE);
            $ro_fromobject = new ilRadioOption($this->txt("settings_mailing_source_object"), self::F_MAILING_FROM_OBJECT);

            $ti = new \ilEMailInputGUI($this->txt("recipient"), self::F_RECIPIENT);
            $ti->setSize(40);
            $ti->setRequired(true);
            $ro_fromobject->addSubItem($ti);

            $ni = new \ilNumberInputGUI($this->txt("send_days_before"), self::F_SEND_DAYS_BEFORE);
            $ni->setMinValue(0, true);
            $ni->setInfo($this->txt("send_days_before_info"));
            $ni->setRequired(true);
            $ro_fromobject->addSubItem($ni);

            $ni = new \ilNumberInputGUI($this->txt("send_reminder_days_before"), self::F_SEND_REMINDER_DAYS_BEFORE);
            $ni->setMinValue(0, true);
            $ni->setInfo($this->txt("send_reminder_days_before_info"));
            $ni->setRequired(true);
            $ro_fromobject->addSubItem($ni);

            $msource->addOption($ro_fromcourse);
            $msource->addOption($ro_fromobject);
        } else {
            $vsource->setAlert($this->txt('alert_venue_plug_not_active'));
            $msource->setAlert($this->txt('alert_venue_plug_not_active'));
        }

        $form->addItem($vsource);
        $form->addItem($msource);

        $cb = new ilCheckboxInputGUI($this->txt("settings_prior_day"), self::F_PRIOR);
        $form->addItem($cb);

        $cb = new ilCheckboxInputGUI($this->txt("settings_following_day"), self::F_FOLLOWING);
        $form->addItem($cb);

        $ni = new ilNumberInputGUI($this->txt("settings_deadline"), self::F_DEADLINE);
        $form->addItem($ni);

        $cb = new ilCheckboxInputGUI($this->txt("settings_edit_notes"), self::F_EDIT_NOTES);
        $cb->setInfo($this->txt("settings_edit_notes_info"));
        $form->addItem($cb);

        return $form;
    }

    /**
     * Fill the settings form
     *
     * @param \ilPropertyFormGUI 	$form
     * @return \ilPropertyFormGUI 	$form
     */
    protected function fillForm(\ilPropertyFormGUI $form) : ilPropertyFormGUI
    {
        $settings = $this->actions->getObjSettings();
        $title = $this->actions->getTitle();
        $desc = $this->actions->getDescription();

        if ($settings->getDatesByCourse() === true) {
            $date_source = self::F_DATE_FROM_COURSE;
        } else {
            $date_source = self::F_DATE_FROM_SETTINGS;
        }

        if ($settings->getLocationFromCourse() === true) {
            $venue_source = self::F_VENUE_FROM_COURSE;
        } else {
            $venue_source = self::F_VENUE_FROM_SELECTION;
        }

        if ($settings->getMailsettingsFromVenue() === true) {
            $mailing_source = self::F_MAILING_FROM_VENUE;
        } else {
            $mailing_source = self::F_MAILING_FROM_OBJECT;
        }

        $start_date = $settings->getStartDate();
        if (!is_null($start_date)) {
            $start_date = $start_date->format("Y-m-d");
        }

        $end_date = $settings->getEndDate();
        if (!is_null($end_date)) {
            $end_date = $end_date->format("Y-m-d");
        }

        $values = array(
            self::F_TITLE => $title,
            self::F_DESCRIPTION => $desc,
            self::F_DATE_SOURCE => $date_source,
            self::F_STARTDATE => $start_date,
            self::F_ENDDATE => $end_date,
            self::F_VENUE => $settings->getLocationObjId(),
            self::F_VENUE_SOURCE => $venue_source,
            self::F_PRIOR => $settings->isPriorDayAllowed(),
            self::F_FOLLOWING => $settings->isFollowingDayAllowed(),
            self::F_DEADLINE => $settings->getBookingEnd(),
            self::F_MAILING_SOURCE => $mailing_source,
            self::F_RECIPIENT => $settings->getMailRecipient(),
            self::F_SEND_DAYS_BEFORE => $settings->getSendDaysBefore(),
            self::F_SEND_REMINDER_DAYS_BEFORE => $settings->getSendReminderDaysBefore(),
            self::F_EDIT_NOTES => $settings->getEditNotes()
        );

        $form->setValuesByArray($values);

        return $form;
    }
}

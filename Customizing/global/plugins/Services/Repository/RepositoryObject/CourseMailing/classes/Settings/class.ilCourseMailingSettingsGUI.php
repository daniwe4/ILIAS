<?php
use \CaT\Plugins\CourseMailing;

require_once("Services/Utilities/classes/class.ilUtil.php");

/**
 * GUI for Settings
 *
 * @author Daniel Weise 	<daniel.weise@concepts-and-training.de>
 */
class ilCourseMailingSettingsGUI
{
    const DEFAULT_CMD_CONTENT = "showContent";
    const CMD_EDIT_SETTINGS = "editSettings";
    const CMD_SAVE_SETTINGS = "saveSettings";
    const CMD_EDIT_PROPERTIES = "editProperties";
    const CMD_SHOW_CHANGE_LOG = "showChangeLog";

    const F_TITLE = "title";
    const F_DESCRIPTION = "description";
    const F_DAYS_INVITE = "dinvt";
    const F_DAYS_INVITE_REMINDER = "dinvtrem";
    const F_PREVENT_MAILING = "prevent_mailing";


    /**
     * @var \ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var \ilGlobalTemplateInterface
     */
    protected $tpl;

    /**
     * @var \ilObjCourseMailingGUI
     */
    protected $parent_object;

    /**
     * @var \ilObjectActions
     */
    protected $actions;

    /**
     * Constructor of the class ilCourseMailingSettingsGUI
     *
     * @param CourseMailing\ilObjCourseMailingGUI 	$parent_object
     * @param CourseMailing\ilActions 				$actions
     * @param Closure 								$txt
     */
    public function __construct($parent_object, $actions, \Closure $txt)
    {
        global $DIC;

        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->g_tabs = $DIC->tabs();
        $this->parent_object = $parent_object;
        $this->actions = $actions;
        $this->txt = $txt;
    }

    /**
     * Delegate commands
     *
     * @throws \Exception
     */
    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd(self::CMD_EDIT_SETTINGS);
        $this->setSubTabs();
        switch ($cmd) {
            case self::DEFAULT_CMD_CONTENT:
            case self::CMD_EDIT_SETTINGS:
            case self::CMD_EDIT_PROPERTIES:
                $this->activateSubTab(self::CMD_EDIT_PROPERTIES);
                $this->editSettings();
                break;
            case self::CMD_SAVE_SETTINGS:
                $this->saveSettings();
                break;
            case self::CMD_SHOW_CHANGE_LOG:
                $this->activateSubTab(self::CMD_SHOW_CHANGE_LOG);
                $this->showChangeLog();
                break;
            default:
                throw new \Exception("unkown command " . $cmd);
        }
    }

    /**
     * Create a editing GUI
     *
     * @param \ilPropertyformGUI 	$form
     */
    protected function editSettings($form = null)
    {
        if ($form === null) {
            $form = $this->initForm();
            $this->fillForm($form);
        }

        $form->addCommandButton(self::CMD_SAVE_SETTINGS, $this->txt("xcml_save"));
        $form->addCommandButton(self::CMD_EDIT_SETTINGS, $this->txt("xcml_cancel"));

        $this->g_tpl->setContent($form->getHtml());
    }

    /**
     * Save settings to db
     */
    protected function saveSettings()
    {
        $form = $this->initForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->editSettings($form);
            return;
        }

        $post = $_POST;
        $settings = $this->actions->getSettings()
            ->withDaysInvitation((int) $post[self::F_DAYS_INVITE])
            ->withDaysRemindInvitation((int) $post[self::F_DAYS_INVITE_REMINDER])
            ->withPreventMailing((bool) $post[self::F_PREVENT_MAILING]);

        $this->actions->updateSettings($settings);

        $this->actions->updateObject($post['title'], $post['description']);
        ilUtil::sendSuccess($this->txt("settings_successful_saved"), true);
        $this->g_ctrl->redirect($this, self::CMD_EDIT_SETTINGS);
    }

    /**
     * Show the log of changes
     *
     * @return void
     */
    protected function showChangeLog()
    {
        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->txt("change_log"));

        $tpl = new ilTemplate("tpl.change_log_entry.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/CourseMailing");
        foreach ($this->actions->getLogEntries() as $log_entry) {
            $tpl->setCurrentBlock("entry");

            $user_name = ilObjUser::_lookupLogin($log_entry->getUserId());
            $row_head = sprintf($this->txt("change_log_row_head"), $log_entry->getChangeDate()->format("d.m.Y H:i:s"), $user_name);
            $tpl->setVariable("ROW_HEAD", $row_head);
            $tpl->setVariable("TEXT_INVITE", $this->txt("settings_days_invitation"));
            $tpl->setVariable("VALUE_INVITE", $log_entry->getValueInvite());
            $tpl->setVariable("TEXT_INVITEREMINDER", $this->txt("settings_days_invitation_reminder"));
            $tpl->setVariable("VALUE_INVITEREMINDER", $log_entry->getValueInvitereminder());
            $tpl->setVariable("TEXT_SUPRESS", $this->txt("settings_prevent_mailing"));

            $supress = $this->txt("no");
            if ($log_entry->getValueSupress()) {
                $supress = $this->txt("yes");
            }
            $tpl->setVariable("VALUE_SUPRESS", $supress);
            $tpl->parseCurrentBlock();
        }

        $ne = new ilNonEditableValueGUI("", "", true);
        $ne->setValue($tpl->get());
        $form->addItem($ne);

        $this->g_tpl->setContent($form->getHtml());
    }

    /**
     * Init a new settings form
     */
    protected function initForm()
    {
        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->g_ctrl->getFormAction($this));
        $form->setTitle($this->txt("settings_header"));

        $ti = new ilTextInputGUI($this->txt("settings_title"), self::F_TITLE);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ta = new ilTextareaInputGUI($this->txt("settings_description"), self::F_DESCRIPTION);
        $form->addItem($ta);

        $ni = new ilNumberInputGUI($this->txt("settings_days_invitation"), self::F_DAYS_INVITE);
        $ni->setInfo($this->txt("settings_days_invitation_desc"));
        $ni->setRequired(true);
        $ni->allowDecimals(false);
        $ni->setMinValue(0, true);
        $form->addItem($ni);

        $ni = new ilNumberInputGUI($this->txt("settings_days_invitation_reminder"), self::F_DAYS_INVITE_REMINDER);
        $ni->setInfo($this->txt("settings_days_invitation_reminder_desc"));
        $ni->setRequired(true);
        $ni->allowDecimals(false);
        $ni->setMinValue(0, true);
        $form->addItem($ni);

        $settings = $this->actions->getSettings();
        $cb = new ilCheckboxInputGUI($this->txt("settings_prevent_mailing"), self::F_PREVENT_MAILING);
        $cb->setInfo($this->txt("settings_prevent_mailing_info"));
        $form->addItem($cb);

        return $form;
    }

    /**
     * Fill the settings form
     *
     * @param \ilPropertyFormGUI 	$form
     */
    protected function fillForm(\ilPropertyFormGUI $form)
    {
        $current = $this->actions->getObject();
        $settings = $this->actions->getSettings();

        $values = array(
            self::F_TITLE => $current->getTitle(),
            self::F_DESCRIPTION => $current->getDescription(),
            self::F_DAYS_INVITE => $settings->getDaysInvitation(),
            self::F_DAYS_INVITE_REMINDER => $settings->getDaysRemindInvitation(),
            self::F_PREVENT_MAILING => $settings->getPreventMailing()
            );

        $form->setValuesByArray($values);
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

    protected function setSubTabs()
    {
        $this->g_tabs->addSubTab(
            self::CMD_EDIT_PROPERTIES,
            $this->txt(self::CMD_EDIT_PROPERTIES),
            $this->g_ctrl->getLinkTarget($this, self::CMD_EDIT_PROPERTIES)
        );

        $this->g_tabs->addSubTab(
            self::CMD_SHOW_CHANGE_LOG,
            $this->txt('change_log'),
            $this->g_ctrl->getLinkTarget($this, self::CMD_SHOW_CHANGE_LOG)
        );
    }

    protected function activateSubTab($tab)
    {
        $this->g_tabs->setSubTabActive($tab);
    }
}

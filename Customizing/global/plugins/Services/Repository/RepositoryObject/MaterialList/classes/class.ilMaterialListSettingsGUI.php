<?php

use \CaT\Plugins\MaterialList\ilObjectActions;

/**
 * Class for the settings of an repo object
 */
class ilMaterialListSettingsGUI
{
    const CMD_EDIT_PROPERTIES = "editProperties";
    const CMD_SAVE_PROPERTIES = "saveProperties";
    const CMD_CANCEL = "cancel";

    /**
     * @var ilObjectActions
     */
    protected $object_actions;

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var \ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var \ilGlobalTemplateInterface
     */
    protected $tpl;

    /**
     * @var \ilObjMaterialListGUI
     */
    protected $parent_gui;

    public function __construct(\ilObjMaterialListGUI $parent_gui, ilObjectActions $object_actions, \Closure $txt)
    {
        global $DIC;

        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();

        $this->object_actions = $object_actions;
        $this->txt = $txt;
        $this->parent_gui = $parent_gui;
    }

    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd();

        switch ($cmd) {
            case self::CMD_EDIT_PROPERTIES:
                $this->editProperties();
                break;
            case self::CMD_SAVE_PROPERTIES:
                $this->saveProperties();
                break;
            case self::CMD_CANCEL:
                $this->cancel();
                break;
            default:
                throw new Exception(__METHOD__ . " :: Unknown command " . $cmd);
        }
    }

    /**
     * Shows edit forms for settings
     *
     * @return null
     */
    protected function editProperties($form = null)
    {
        if ($form === null) {
            $form = $this->initForm();
            $this->fillForm($form);
        }

        $this->g_tpl->setContent($form->getHtml());
    }

    /**
     * Init form for settings
     *
     * @return \ilPropertyFormGUI
     */
    protected function initForm()
    {
        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new \ilPropertyFormGUI();
        $form->setTitle($this->txt("settings_form_title"));
        $form->setFormAction($this->g_ctrl->getFormAction($this));
        $form->addCommandButton(self::CMD_SAVE_PROPERTIES, $this->txt("save"));
        $form->addCommandButton(self::CMD_CANCEL, $this->txt("cancel"));

        $ti = new \ilTextInputGUI($this->txt("settings_title"), ilObjectActions::F_SETTINGS_TITLE);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ta = new \ilTextAreaInputGUI($this->txt("settings_description"), ilObjectActions::F_SETTINGS_DESCRIPTION);
        $form->addItem($ta);

        $rg = new \ilRadioGroupInputGUI($this->txt("recipient_mode"), ilObjectActions::F_RECIPIENT_MODE);
        $opt = new \ilRadioOption($this->txt('course_venue'), ilObjectActions::M_COURSE_VENUE);
        $rg->addOption($opt);
        $opt = new \ilRadioOption($this->txt('selection'), ilObjectActions::M_SELECTION);
        $ti = new \ilEMailInputGUI($this->txt("recipient"), ilObjectActions::F_RECIPIENT);
        $ti->setInfo($this->txt("recipient_info"));
        $ti->setSize(40);
        $ti->setRequired(true);
        $opt->addSubItem($ti);
        $ni = new \ilNumberInputGUI($this->txt("send_days_before"), ilObjectActions::F_SEND_DAYS_BEFORE);
        $ni->setMinValue(0, true);
        $ni->setInfo($this->txt("send_days_before_info"));
        $ni->setRequired(true);
        $opt->addSubItem($ni);
        $rg->addOption($opt);

        $form->addItem($rg);

        return $form;
    }

    /**
     * Fill form with current values
     *
     * @param \ilPropertyFormGUI 	$form
     *
     * @return null
     */
    protected function fillForm(\ilPropertyFormGUI $form)
    {
        $values = $this->object_actions->getSettingsValues();
        $form->setValuesByArray($values);
    }

    /**
     * Save new settings
     *
     * @return null
     */
    protected function saveProperties()
    {
        $form = $this->initForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->editProperties($form);
            return;
        }

        $this->object_actions->updateSettings($_POST);

        \ilUtil::sendSuccess($this->txt("save_settings_success"), true);
        $this->g_ctrl->redirect($this, self::CMD_EDIT_PROPERTIES);
    }

    /**
     * Revoke user entries
     *
     * @return null
     */
    protected function cancel()
    {
        $this->g_ctrl->redirect($this, self::CMD_EDIT_PROPERTIES);
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

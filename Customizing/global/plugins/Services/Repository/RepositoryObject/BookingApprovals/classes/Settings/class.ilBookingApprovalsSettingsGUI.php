<?php

declare(strict_types=1);

require_once "Services/Form/classes/class.ilPropertyFormGUI.php";

use CaT\Plugins\BookingApprovals\ilObjectActions;

/**
 * Class for the settings of an repo object
 */
class ilBookingApprovalsSettingsGUI
{
    const CMD_EDIT_PROPERTIES = "editProperties";
    const CMD_SAVE_PROPERTIES = "saveProperties";
    const CMD_CANCEL = "cancel";

    const F_SETTINGS_TITLE = "settings_title";
    const F_SETTINGS_DESCRIPTION = "settings_description";
    const F_SETTINGS_SUPERIOR_VIEW = "settings_superior_view";

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
     * @var \ilObBookingApprovalsGUI
     */
    protected $parent_gui;

    public function __construct(
        \ilObjBookingApprovalsGUI $parent_gui,
        ilObjectActions $object_actions,
        \Closure $txt
    ) {
        global $DIC;

        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();

        $this->object_actions = $object_actions;
        $this->txt = $txt;
        $this->parent_gui = $parent_gui;
    }

    /**
     * @throws Exception
     */
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
     */
    protected function editProperties(ilPropertyFormGUI $form = null)
    {
        if ($form === null) {
            $form = $this->initForm();
            $this->fillForm($form);
        }

        $this->g_tpl->setContent($form->getHtml());
    }

    protected function initForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->txt("settings_form_title"));
        $form->setFormAction($this->g_ctrl->getFormAction($this));
        $form->addCommandButton(self::CMD_SAVE_PROPERTIES, $this->txt("save"));
        $form->addCommandButton(self::CMD_CANCEL, $this->txt("cancel"));

        $ti = new \ilTextInputGUI($this->txt("settings_title"), self::F_SETTINGS_TITLE);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ta = new \ilTextAreaInputGUI($this->txt("settings_description"), self::F_SETTINGS_DESCRIPTION);
        $form->addItem($ta);

        $cb = new \ilCheckboxInputGUI($this->txt("settings_superior_view"), self::F_SETTINGS_SUPERIOR_VIEW);
        $form->addItem($cb);

        return $form;
    }

    /**
     * Fill form with current values
     */
    protected function fillForm(\ilPropertyFormGUI $form)
    {
        $values = $this->object_actions->getSettingsValues();
        $form->setValuesByArray($values);
    }

    /**
     * Save new settings
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
    public function txt(string $code) : string
    {
        $txt = $this->txt;
        return $txt($code);
    }
}

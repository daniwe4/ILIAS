<?php

use CaT\Plugins\EduTracking;

require_once __DIR__ . "/../Purposes/WBD/class.ilWBDGUI.php";
require_once __DIR__ . "/../Purposes/IDD/class.ilIDDGUI.php";
require_once __DIR__ . "/../Purposes/GTI/class.ilGTIGUI.php";

/**
 * Settings gui for repository objects of edu tracking
 *
 * @ilCtrl_Calls ilEduTrackingSettingsGUI: ilWBDGUI, ilIDDGUI, ilGTIGUI
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */

class ilEduTrackingSettingsGUI
{
    const CMD_EDIT_PROPERTIES = "editProperties";
    const CMD_SAVE = "saveProperties";

    const F_TITLE = "f_title";
    const F_DESCRIPTION = "f_description";

    const TAB_WBD = "tab_wbd";
    const TAB_IDD = "tab_idd";
    const TAB_GTI = "tab_gti";
    const TAB_SETTINGS = "tab_settings";

    /**
     * @var ilObjEduTrackingGUI
     */
    protected $parent;

    /**
     * @var ilObjEduTracking
     */
    protected $object;

    /**
     * @var ilEduTrackingPlugin
     */
    protected $plugin;

    /**
     * @var EduTracking\ilObjActions
     */
    protected $actions;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $g_tpl;

    /**
     * @var ilCtrl
     */
    protected $g_ctrl;

    public function __construct(ilObjEduTrackingGUI $parent, ilObjEduTracking $object, ilEduTrackingPlugin $plugin)
    {
        $this->parent = $parent;
        $this->object = $object;
        $this->actions = $this->object->getActions();
        $this->plugin = $plugin;

        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->g_tabs = $DIC->tabs();
        $this->g_access = $DIC->access();
    }

    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd();
        $next_class = $this->g_ctrl->getNextClass();
        $this->setSubTabs();
        switch ($next_class) {
            case "ilwbdgui":
                $this->activateSubTab(self::TAB_WBD);
                $actions = $this->object->getActionsFor("WBD");
                $config_actions = $this->plugin->getConfigActionsFor("WBD");
                $gui = new ilWBDGUI($this, $actions, $config_actions, $this->object->getParentCourse());
                $this->g_ctrl->forwardCommand($gui);
                break;
            case "iliddgui":
                $this->activateSubTab(self::TAB_IDD);
                $actions = $this->object->getActionsFor("IDD");
                $config_actions = $this->plugin->getConfigActionsFor("IDD");
                $gui = new ilIDDGUI($this, $actions, $config_actions, $this->object->getParentCourse());
                $this->g_ctrl->forwardCommand($gui);
                break;
            case "ilgtigui":
                $this->activateSubTab(self::TAB_GTI);
                $actions = $this->object->getActionsFor("GTI");
                $config_actions = $this->plugin->getConfigActionsFor("GTI");
                $gui = new ilGTIGUI($this, $actions, $config_actions, $this->object->getParentCourse());
                $this->g_ctrl->forwardCommand($gui);
                break;
            default:
                switch ($cmd) {
                    case self::CMD_EDIT_PROPERTIES:
                        $this->activateSubTab(self::TAB_SETTINGS);
                        $this->edit();
                        break;
                    case self::CMD_SAVE:
                        $this->save();
                        break;
                    default:
                        throw new Exception("Unknown command: " . $cmd);
                }
        }
    }

    /**
     * Show the current settings
     *
     * @param ilPropertyFormGUI | null 	$form
     *
     * @return void
     */
    protected function edit(ilPropertyFormGUI $form = null)
    {
        if ($form === null) {
            $form = $this->initForm();
            $this->fillForm($form);
        }

        $form->addCommandButton(self::CMD_SAVE, $this->txt("save"));
        $form->addCommandButton(self::CMD_EDIT_PROPERTIES, $this->txt("cancel"));

        $this->g_tpl->setContent($form->getHtml());
    }

    /**
     * Save the settings
     *
     * @return void
     */
    protected function save()
    {
        $form = $this->initForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->edit($form);
            return;
        }

        $post = $_POST;
        $title = $post[self::F_TITLE];
        $description = $post[self::F_DESCRIPTION];
        $object = $this->actions->getObject();
        $object->setTitle($title);
        $object->setDescription($description);
        $object->update();

        ilUtil::sendSuccess($this->txt("settings_saved"), true);
        $this->g_ctrl->redirect($this, self::CMD_EDIT_PROPERTIES);
    }

    /**
     * Inits the settings for,
     *
     * @return ilPropertyFormGUI
     */
    protected function initForm()
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->txt("settings"));
        $form->setFormAction($this->g_ctrl->getFormAction($this));

        $ti = new ilTextInputGUI($this->txt("title"), self::F_TITLE);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ta = new ilTextareaInputGUI($this->txt("description"), self::F_DESCRIPTION);
        $form->addItem($ta);

        return $form;
    }

    /**
     * Fills the form with current values
     *
     * @param ilPropertyFormGUI
     *
     * @return void
     */
    protected function fillForm(ilPropertyFormGUI $form)
    {
        $values = array();
        $object = $this->actions->getObject();

        $values[self::F_TITLE] = $object->getTitle();
        $values[self::F_DESCRIPTION] = $object->getDescription();

        $form->setValuesByArray($values);
    }

    /**
     * Set Subtabs for purposes
     *
     * @return void
     */
    protected function setSubTabs()
    {
        $link = $this->g_ctrl->getLinkTarget($this, self::CMD_EDIT_PROPERTIES);
        $this->g_tabs->addSubTab(self::TAB_SETTINGS, $this->txt(self::TAB_SETTINGS), $link);

        $config_actions = $this->plugin->getConfigActionsFor("WBD");
        $settings = $config_actions->select();
        $edit_purpose = $this->g_access->checkAccess("edit_purposes", "", $this->object->getRefId());
        if ($edit_purpose && $settings !== null && $settings->getAvailable()) {
            $link = $this->g_ctrl->getLinkTargetByClass("ilWBDGUI", ilWBDGUI::CMD_EDIT);
            $this->g_tabs->addSubTab(self::TAB_WBD, $this->txt(self::TAB_WBD), $link);
        }

        $config_actions = $this->plugin->getConfigActionsFor("IDD");
        $settings = $config_actions->select();
        if ($edit_purpose && $settings !== null && $settings->getAvailable()) {
            $link = $this->g_ctrl->getLinkTargetByClass("ilIDDGUI", ilIDDGUI::CMD_EDIT);
            $this->g_tabs->addSubTab(self::TAB_IDD, $this->txt(self::TAB_IDD), $link);
        }

        $config_actions = $this->plugin->getConfigActionsFor("GTI");
        $settings = $config_actions->select();
        if ($edit_purpose && $settings !== null && $settings->getAvailable()) {
            $link = $this->g_ctrl->getLinkTargetByClass("ilGTIGUI", ilGTIGUI::CMD_EDIT);
            $this->g_tabs->addSubTab(self::TAB_GTI, $this->txt(self::TAB_GTI), $link);
        }
    }

    /**
     * Activates the sub tab
     *
     * @return void
     */
    protected function activateSubTab($cmd)
    {
        $this->g_tabs->activateSubTab($cmd);
    }

    /**
     * @param 	string	$code
     * @return	string
     */
    protected function txt($code)
    {
        return $this->actions->getObject()->pluginTxt($code);
    }
}

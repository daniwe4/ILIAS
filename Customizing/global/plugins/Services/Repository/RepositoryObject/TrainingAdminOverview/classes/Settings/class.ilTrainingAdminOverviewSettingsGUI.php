<?php

declare(strict_types=1);

use CaT\Plugins\TrainingAdminOverview;
use CaT\Plugins\TrainingAdminOverview\Settings\Settings;

/**
 * Settings for repository object
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilTrainingAdminOverviewSettingsGUI
{
    const CMD_EDIT_PROPERTIES = "editProperties";
    const CMD_SAVE_PROPERTIES = "saveProperties";

    const F_TITLE = "f_title";
    const F_DESCRIPTION = "f_description";
    const F_SHOW_INFO_TAB = "f_show_info_tab";

    /**
     * @var \ilTemplate
     */
    protected $g_tpl;

    /**
     * @var \ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var \ilTrainingAdminOverviewGUI
     */
    protected $parent;

    /**
     * @var ilAccess
     */
    protected $g_access;

    /**
     * @var TrainingAdminOverview\ilObjActions 	$actions
     */
    protected $actions;

    public function __construct(ilObjTrainingAdminOverviewGUI $parent, TrainingAdminOverview\ilObjActions $actions)
    {
        global $DIC;
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->g_ctrl = $DIC->ctrl();
        $this->g_access = $DIC->access();

        $this->parent = $parent;
        $this->actions = $actions;
    }

    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd();

        switch ($cmd) {
            case self::CMD_EDIT_PROPERTIES:
                if ($this->g_access->checkAccess("write", "", $this->parent->object->getRefId())) {
                    $this->editProperties();
                } else {
                    \ilUtil::redirect("");
                }
                break;
            case self::CMD_SAVE_PROPERTIES:
                if ($this->g_access->checkAccess("write", "", $this->parent->object->getRefId())) {
                    $this->saveProperties();
                } else {
                    \ilUtil::redirect("");
                }
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    /**
     * Displays existing settings
     *
     * @param ilPropertyFormGUI | null 	$form
     *
     * @return void
     */
    protected function editProperties(ilPropertyFormGUI $form = null)
    {
        if ($form === null) {
            $form = $this->initForm();
            $this->fillForm($form);
        }

        $this->g_tpl->setContent($form->getHtml());
    }

    /**
     * Save actual settings
     *
     * @return void
     */
    protected function saveProperties()
    {
        $form = $this->initForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->editProperties($form);
            return;
        }

        $post = $_POST;
        $show_info_tab = (bool) $post[self::F_SHOW_INFO_TAB];
        $fnc = function (Settings $s) use ($show_info_tab) {
            return $s->withShowInfoTab($show_info_tab);
        };
        $object = $this->actions->getObject();
        $object->setTitle($post[self::F_TITLE]);
        $object->setDescription($post[self::F_DESCRIPTION]);
        $object->updateSettings($fnc);
        $object->update();

        $this->g_ctrl->redirect($this, self::CMD_EDIT_PROPERTIES);
    }

    /**
     * Inits the settings form
     *
     * @return ilPropertyFormGUI
     */
    protected function initForm()
    {
        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->txt("form_title"));
        $form->setFormAction($this->g_ctrl->getFormAction($this));

        $ti = new ilTextInputGUI($this->txt("title"), self::F_TITLE);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ta = new ilTextareaInputGUI($this->txt("description"), self::F_DESCRIPTION);
        $form->addItem($ta);

        $cb = new ilCheckboxInputGUI($this->txt('show_info_tab'), self::F_SHOW_INFO_TAB);
        $cb->setInfo($this->txt('show_info_tab_byline'));
        $form->addItem($cb);

        $form->addCommandButton(self::CMD_SAVE_PROPERTIES, $this->txt("save"));
        $form->addCommandButton(self::CMD_EDIT_PROPERTIES, $this->txt("cancel"));

        return $form;
    }

    /**
     * Fills form with current values
     *
     * @param ilPropertyFormGUI 	$form
     */
    protected function fillForm(ilPropertyFormGUI $form)
    {
        $values = array();

        $object = $this->actions->getObject();

        $values[self::F_TITLE] = $object->getTitle();
        $values[self::F_DESCRIPTION] = $object->getDescription();
        $values[self::F_SHOW_INFO_TAB] = $object->getSettings()->getShowInfoTab();

        $form->setValuesByArray($values);
    }

    /**
     * @param 	string	$code
     * @return	string
     */
    protected function txt($code)
    {
        assert('is_string($code)');
        return $this->actions->getObject()->pluginTxt($code);
    }
}

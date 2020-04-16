<?php

use CaT\Plugins\EduTracking\Purposes\IDD;

/**
 * Repository configuration for IDD purpose
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilIDDGUI
{
    const CMD_EDIT = "editIDD";
    const CMD_SAVE = "saveIDD";

    const F_HOURES = "f_houres";

    /**
     * @var ilEduTrackingSettingsGUI
     */
    protected $parent;

    /**
     * @var IDD\ilObjActions
     */
    protected $actions;

    /**
     * @var IDD\Configuration\ilActions
     */
    protected $config_actions;

    /**
     * @var ilObjCourse
     */
    protected $parent_course;

    /**
     * @var ilTemplate
     */
    protected $g_tpl;

    /**
     * @var ilCtrl
     */
    protected $g_ctrl;

    /**
     * @param bool 	$below_course
     */
    public function __construct(
        ilEduTrackingSettingsGUI $parent,
        IDD\ilActions $actions,
        IDD\Configuration\ilActions $config_actions,
        ilObjCourse $parent_course = null
    ) {
        $this->parent = $parent;
        $this->actions = $actions;
        $this->parent_course = $parent_course;
        $this->config_actions = $config_actions;
        $this->below_course = $parent_course !== null;

        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();
    }

    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_EDIT:
                $this->edit();
                break;
            case self::CMD_SAVE:
                $this->save();
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    /**
     * Edit current settings
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
        $form->addCommandButton(self::CMD_EDIT, $this->txt("cancel"));

        $this->g_tpl->setContent($form->getHtml());
    }

    /**
     * Save current settings
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
        $learning_time = $post[self::F_HOURES];

        $minutes = $learning_time["mm"] + $learning_time["hh"] * 60;
        $settings = $this->actions->select();
        $settings = $settings->withMinutes((int) $minutes);
        $settings->update();


        ilUtil::sendSuccess($this->txt("idd_settings_saved"), true);
        $this->g_ctrl->redirect($this, self::CMD_EDIT);
    }

    /**
     * Init the form if object is below course
     *
     * @return ilPropertyFormGUI
     */
    protected function initForm()
    {
        $form = $this->getForm();

        require_once("Services/Form/classes/class.ilTimeInputGUI.php");
        $ni = new \ilTimeInputGUI("", self::F_HOURES);
        $ni->setMaxHours(250);
        $ni->setMinuteStepSize(5);
        $form->addItem($ni);

        return $form;
    }

    /**
     * Fills form with values
     *
     * @param ilPropertyFormGUI
     *
     * @return void
     */
    protected function fillForm(ilPropertyFormGUI $form)
    {
        $settings = $this->actions->select();
        $values = array();

        $minutes = $settings->getMinutes();
        if ($minutes !== null) {
            $hours = floor($minutes / 60);
            $minutes = $minutes - $hours * 60;
            $values[self::F_HOURES]["hh"] = $hours;
            $values[self::F_HOURES]["mm"] = $minutes;
        }

        $form->setValuesByArray($values);
    }

    /**
     * Get form with basic configuration
     *
     * @return ilPropertyFormGUI
     */
    protected function getForm()
    {
        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->g_ctrl->getFormAction($this));
        $form->setTitle($this->txt("idd_settings"));

        return $form;
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

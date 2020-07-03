<?php

/**
 * Settings gui for learning progress
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilLPSettingsGUI
{
    const CMD_LP = "editLP";
    const CMD_UPDATE_LP = "updateLP";
    const F_LP_MODE = "f_lp_mode";

    public function __construct($parent, $actions)
    {
        global $ilCtrl, $tpl, $lng;

        $this->gCtrl = $ilCtrl;
        $this->gTpl = $tpl;
        $this->gLng = $lng;
        $this->parent = $parent;

        $this->actions = $actions;
    }

    public function executeCommand()
    {
        $cmd = $this->gCtrl->getCmd();

        switch ($cmd) {
            case self::CMD_LP:
            case self::CMD_UPDATE_LP:
                $this->$cmd();
                break;
            default:
                throw new Exception("CSNLPSettingsGUI:: Unknown command " . $cmd);
        }
    }

    protected function editLP()
    {
        $form = $this->initLPForm();
        $this->fillLPForm($form);
        $this->gTpl->setContent($form->getHTML());
    }

    protected function updateLP()
    {
        $form = $this->initLPForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->gTpl->setContent($form->getHTML());
            return;
        }

        $post = $_POST;
        $fnc = function ($s) use ($post) {
            return $s->withLPMode((int) $post[self::F_LP_MODE]);
        };

        $this->actions->getObject()->updateSettings($fnc);
        $this->actions->getObject()->update();

        $this->actions->refreshLP();

        ilUtil::sendSuccess($this->txt('obj_msg_lp_form_saved'), true);
        $this->gCtrl->redirect($this, self::CMD_LP);
    }

    protected function initLPForm()
    {
        $this->gLng->loadLanguageModule('trac');
        include_once("Services/Tracking/classes/class.ilLPObjSettings.php");

        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->txt('obj_edit_lp'));

        $lp_mode = new ilRadioGroupInputGUI($this->txt('obj_lp_mode'), self::F_LP_MODE);
        $lp_mode->setRequired(true);
        $form->addItem($lp_mode);

        foreach ($this->getValidLPModes() as $mode) {
            if ($mode == ilLPObjSettings::LP_MODE_PLUGIN) {
                $opt = new ilRadioOption(
                    $this->txt('obj_lp_mode_text'),
                    $mode,
                    $this->txt('obj_lp_mode_info_text')
                );
            } else {
                $opt = new ilRadioOption(
                    ilLPObjSettings::_mode2Text($mode),
                    $mode,
                    ilLPObjSettings::_mode2InfoText($mode)
                );
            }

            $lp_mode->addOption($opt);
        }

        $form->addCommandButton(self::CMD_UPDATE_LP, $this->txt('obj_save'));
        $form->setFormAction($this->gCtrl->getFormAction($this));

        return $form;
    }

    /**
     * Get all valid lp modes
     *
     * @return int[]
     */
    public function getValidLPModes()
    {
        return array(
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_PLUGIN
        );
    }

    /**
     * Fill form with current lp settings
     *
     * @param ilPropertyFormGUI 	$form
     *
     * @return null
     */
    protected function fillLPForm($form)
    {
        $values = array();

        $values[self::F_LP_MODE] = $this->actions->getObject()->getSettings()->getLPMode();

        $form->setValuesByArray($values);
    }

    /**
     * Translate code
     *
     * @param string 	$code
     *
     * @return string
     */
    protected function txt($code)
    {
        return $this->actions->getObject()->pluginTxt($code);
    }
}

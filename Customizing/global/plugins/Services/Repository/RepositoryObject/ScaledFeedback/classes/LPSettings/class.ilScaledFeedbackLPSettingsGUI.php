<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */
/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Plugins\ScaledFeedback\LPSettings\LPManager;

class ilScaledFeedbackLPSettingsGUI
{
    const CMD_LP = "editLP";
    const CMD_UPDATE_LP = "updateLP";
    const F_LP_MODE = "f_lp_mode";

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var \ilObjScaledFeedback
     */
    protected $object;

    /**
     * @var LPManager
     */
    protected $lp_manager;

    /**
     * @var Closure
     */
    protected $txt;

    public function __construct(
        ilCtrl $ctrl,
        ilTemplate $tpl,
        ilLanguage $lng,
        \ilObject $object,
        LPManager $lp_manager,
        Closure $txt
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->object = $object;
        $this->lp_manager = $lp_manager;
        $this->txt = $txt;
    }

    /**
     * @throws Exception
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();

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
        $this->tpl->setContent($form->getHTML());
    }

    protected function updateLP()
    {
        $form = $this->initLPForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());
            return;
        }

        $post = $_POST;
        $fnc = function ($s) use ($post) {
            return $s->withLPMode((int) $post[self::F_LP_MODE]);
        };

        $this->object->updateSettings($fnc);
        $this->object->update();

        if (!is_null($this->object->getParentRefId())) {
            $this->lp_manager->refresh((int) $this->object->getId());
        }

        ilUtil::sendSuccess($this->txt('obj_msg_lp_form_saved'), true);
        $this->ctrl->redirect($this, self::CMD_LP);
    }

    protected function initLPForm() : ilPropertyFormGUI
    {
        $this->lng->loadLanguageModule('trac');
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
        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }

    /**
     * Get all valid lp modes
     *
     * @return int[]
     */
    public function getValidLPModes() : array
    {
        return array(
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_PLUGIN
        );
    }

    /**
     * Fill form with current lp settings
     */
    protected function fillLPForm(ilPropertyFormGUI $form)
    {
        $values = array();

        $values[self::F_LP_MODE] = $this->object->getSettings()->getLPMode();
        $form->setValuesByArray($values);
    }

    protected function txt(string $code) : string
    {
        $txt = $this->txt;
        return $txt($code);
    }
}

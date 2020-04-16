<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

require_once "Services/Form/classes/class.ilPropertyFormGUI.php";
require_once "Services/Form/classes/class.ilTextInputGUI.php";

use \CaT\Plugins\ScaledFeedback;

/**
 * Class ilSFSettingsGUI.
 * GUI for general settings of a Feedback.
 */
class ilSFSettingsGUI
{
    const CMD_SHOW = "showContent";
    const CMD_CANCEL = "cancel";
    const CMD_SAVE_SETTINGS = "saveSettings";
    const CMD_SETTINGS = "showSettings";

    const F_TITLE = "title";
    const F_DESCRIPTION = "description";
    const F_IS_ONLINE = "is_online";
    const F_SET_ID = "set_id";

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ScaledFeedback\Feedback\DB
     */
    protected $feedback_db;

    /**
     * @var ScaledFeedback\Config\DB
     */
    protected $config_db;

    /**
     * @var \ilObjScaledFeedback
     */
    protected $object;

    /**
     * @var string
     */
    protected $cancel_link;

    /**
     * @var Closure
     */
    protected $txt;

    public function __construct(
        ilCtrl $ctrl,
        ilTemplate $tpl,
        ScaledFeedback\Feedback\DB $feedback_db,
        ScaledFeedback\Config\DB $config_db,
        \ilObject $object,
        string $cancel_link,
        Closure $txt
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->feedback_db = $feedback_db;
        $this->config_db = $config_db;
        $this->object = $object;
        $this->cancel_link = $cancel_link;
        $this->txt = $txt;
    }

    /**
     * @throws Exception
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        if ($cmd == null) {
            $cmd = self::CMD_SHOW;
        }
        switch ($cmd) {
            case self::CMD_SHOW:
            case self::CMD_SETTINGS:
                $this->showSettings();
                break;
            case self::CMD_SAVE_SETTINGS:
                $this->saveSettings();
                break;
            case self::CMD_CANCEL:
                $this->cancelSettings();
                break;
            default:
                throw new Exception(__METHOD__ . " unknown command " . $cmd);
        }
    }

    /**
     * Show the setting gui.
     */
    protected function showSettings(ilPropertyFormGUI $form = null)
    {
        if ($form == null) {
            $form = $this->getForm();
            $this->fillForm($form);
        }
        $this->tpl->setContent($form->getHtml());
    }

    /**
     * Save settings.
     */
    protected function saveSettings()
    {
        $post = $_POST;

        $form = $this->getForm();
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showSettings($form);
            return;
        }

        $title = $post[self::F_TITLE];
        $description = $post[self::F_DESCRIPTION];
        $set_id = (int) $post[self::F_SET_ID];
        $is_online = false;

        if (isset($post[self::F_IS_ONLINE]) && $post[self::F_IS_ONLINE] == 1) {
            $is_online = true;
        }

        $fnc = function ($s) use ($set_id, $is_online) {
            $s = $s
                ->withSetId($set_id)
                ->withOnline($is_online);
            return $s;
        };

        $obj = $this->object;
        $obj->setTitle($title);
        $obj->setDescription($description);
        $obj->updateSettings($fnc);
        $obj->update();

        ilUtil::sendSuccess($this->txt("settings_saved"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW);
    }

    protected function cancelSettings()
    {
        $this->ctrl->redirectToURL($this->cancel_link);
    }

    protected function fillForm(ilPropertyFormGUI $form)
    {
        $obj = $this->object;
        $arr = [
            self::F_TITLE => $obj->getTitle(),
            self::F_DESCRIPTION => $obj->getDescription(),
            self::F_SET_ID => $obj->getSettings()->getSetId(),
            self::F_IS_ONLINE => $obj->getSettings()->getOnline()
        ];

        $form->setValuesByArray($arr);
    }

    protected function getForm() : ilPropertyFormGUI
    {
        $obj = $this->object;
        $set_id = $obj->getSettings()->getSetId();
        $feedbacks = $this->feedback_db->selectByIds((int) $obj->getId(), $set_id);

        $disable = false;
        if ($feedbacks != null) {
            $disable = true;
        }

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->txt("set_settings"));
        $form->setShowTopButtons(true);
        $form->addCommandButton(self::CMD_SAVE_SETTINGS, $this->txt("save"));
        $form->addCommandButton(self::CMD_CANCEL, $this->txt("cancel"));

        $ti = new ilTextInputGUI($this->txt("title"), self::F_TITLE);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ta = new ilTextAreaInputGUI($this->txt("description"), self::F_DESCRIPTION);
        $form->addItem($ta);

        $si = new ilSelectInputGUI($this->txt("question_set"), self::F_SET_ID);
        $options = array(null => $this->txt("please_select"));
        $options = $options + $this->config_db->getQuestionSetValues();
        $si->setOptions($options);
        $si->setDisabled($disable);
        $si->setRequired(true);
        $form->addItem($si);

        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->txt("availability"));
        $form->addItem($sh);

        $ci = new ilCheckBoxInputGUI($this->txt("settings_online"), self::F_IS_ONLINE);
        $ci->setInfo($this->txt("settings_online_info"));
        $form->addItem($ci);

        return $form;
    }

    /**
     * Translate code to lang value
     */
    protected function txt(string $code) : string
    {
        $txt = $this->txt;
        return $txt($code);
    }
}

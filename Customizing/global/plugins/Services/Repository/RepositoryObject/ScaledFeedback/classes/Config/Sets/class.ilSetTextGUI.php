<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

require_once "Services/Form/classes/class.ilPropertyFormGUI.php";
require_once "Services/Form/classes/class.ilFormSectionHeaderGUI.php";
require_once "Services/Form/classes/class.ilTextInputGUI.php";

use CaT\Plugins\ScaledFeedback\Config\DB;

class ilSetTextGUI
{
    const CMD_SHOW_SET_TEXT = "showSetText";
    const CMD_SAVE_SET_TEXT = "saveSetText";
    const CMD_CANCEL = "cancel";

    const F_SET_TEXT_INTRO = "set_text_intro";
    const F_SET_TEXT_EXTRO = "set_text_extro";
    const F_SET_TEXT_REPEAT = "set_text_repeat";

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var \ilTemplate
     */
    protected $tpl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var DB
     */
    protected $db;

    /**
     * @var string
     */
    protected $cancel_link;

    /**
     * @var \Closure
     */
    protected $txt;

    public function __construct(
        ilCtrl $ctrl,
        ilTemplate $tpl,
        ilLanguage $lng,
        DB $db,
        string $cancel_link,
        Closure $txt
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->db = $db;
        $this->cancel_link = $cancel_link;
        $this->txt = $txt;
    }

    /**
     * @throws Exception
     */
    public function executeCommand()
    {
        $this->tpl->setTitle($this->lng->txt("cmps_plugin") . ": " . $_GET["pname"] . " Set: " . $this->getSetTitle());

        $cmd = $this->ctrl->getCmd(self::CMD_SHOW_SET_TEXT);
        switch ($cmd) {
            case self::CMD_SHOW_SET_TEXT:
                $this->showContent();
                break;
            case self::CMD_SAVE_SET_TEXT:
                $this->saveSetText();
                break;
            case self::CMD_CANCEL:
                $this->cancel();
                break;
            default:
                throw new Exception(__METHOD__ . " unknown command " . $cmd);
        }
    }

    protected function showContent(ilPropertyFormGUI $form = null)
    {
        if ($form === null) {
            $form = $this->getForm();
            $this->fillForm($form);
        }
        $this->tpl->setContent($form->getHtml());
    }

    protected function saveSetText()
    {
        $form = $this->getForm();
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showContent($form);
            return;
        }
        $post = $_POST;
        $set = $this->db->selectSetById($this->getValidatedIdFromGet());
        $set = $set
            ->withIntrotext($post[self::F_SET_TEXT_INTRO])
            ->withExtrotext($post[self::F_SET_TEXT_EXTRO])
            ->withRepeattext($post[self::F_SET_TEXT_REPEAT]);
        $this->db->updateSet($set);

        \ilUtil::sendSuccess($this->txt("edit_successful"));
        $this->showContent();
    }

    protected function cancel()
    {
        $this->ctrl->redirectToURL($this->cancel_link);
    }

    protected function getForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $this->ctrl->setParameter($this, "id", $this->getValidatedIdFromGet());
        $form->setFormAction($this->ctrl->getFormAction($this));
        $this->ctrl->clearParameters($this);
        $form->setTitle($this->txt("set_text"));
        $form->setShowTopButtons(true);

        $form->addCommandButton(self::CMD_SAVE_SET_TEXT, $this->txt("save"));
        $form->addCommandButton(self::CMD_CANCEL, $this->txt("cancel"));

        $ti = new ilTextAreaInputGUI($this->txt("set_text_intro"), self::F_SET_TEXT_INTRO);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ti = new ilTextAreaInputGUI($this->txt("set_text_extro"), self::F_SET_TEXT_EXTRO);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ti = new ilTextAreaInputGUI($this->txt("set_text_repeat"), self::F_SET_TEXT_REPEAT);
        $ti->setRequired(true);
        $form->addItem($ti);

        return $form;
    }

    protected function fillForm(ilPropertyFormGUI $form)
    {
        $set = $this->db->selectSetById($this->getValidatedIdFromGet());
        $sets = [
            self::F_SET_TEXT_INTRO => $set->getIntrotext(),
            self::F_SET_TEXT_EXTRO => $set->getExtrotext(),
            self::F_SET_TEXT_REPEAT => $set->getRepeattext()
        ];
        $form->setValuesByArray($sets);
    }

    protected function getSet()
    {
        $id = $this->getValidatedIdFromGet();
        if ($id == -1) {
            return false;
        }
        return $this->db->selectSetById($id);
    }

    protected function getValidatedIdFromGet() : int
    {
        if (isset($_GET['id'])) {
            return (int) $_GET['id'];
        }
        return -1;
    }

    protected function getSetTitle() : string
    {
        $set = $this->getSet();
        if (!$set) {
            return "";
        }
        return $set->getTitle();
    }

    protected function txt(string $code) : string
    {
        $txt = $this->txt;
        return $txt($code);
    }
}

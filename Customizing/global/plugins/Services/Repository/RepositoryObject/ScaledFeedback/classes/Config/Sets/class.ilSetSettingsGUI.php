<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

require_once "Services/Form/classes/class.ilPropertyFormGUI.php";
require_once "Services/Form/classes/class.ilTextInputGUI.php";

use CaT\Plugins\ScaledFeedback\Config\DB;

class ilSetSettingsGUI
{
    const CMD_SAVE_SET_SETTINGS = "saveSettings";
    const CMD_UPDATE_SET_SETTINGS = "updateSettings";
    const CMD_ADD_SET = "addSet";
    const CMD_EDIT_SET = "editSet";
    const CMD_CANCEL = "cancel";

    const F_TITLE = "title";
    const F_MIN_SUBMISSIONS = "min_submissions";
    const F_IS_LOCKED = "is_locked";

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilGlobalTemplateInterface
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
     * @var Closure
     */
    protected $txt;

    public function __construct(
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
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
        $cmd = $this->ctrl->getCmd();
        $this->tpl->setTitle($this->lng->txt("cmps_plugin") . ": " . $_GET["pname"] . $this->getSetTitle());

        switch ($cmd) {
            case self::CMD_ADD_SET:
                $this->addSet();
                break;
            case self::CMD_EDIT_SET:
                $this->editSet();
                break;
            case self::CMD_SAVE_SET_SETTINGS:
                $this->saveSettings();
                break;
            case self::CMD_UPDATE_SET_SETTINGS:
                $this->updateSettings();
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
        }
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHtml());
    }

    protected function addSet(ilPropertyFormGUI $form = null)
    {
        if ($form === null) {
            $form = $this->getForm();
        }
        $form->addCommandButton(self::CMD_SAVE_SET_SETTINGS, $this->txt("save"));
        $form->addCommandButton(self::CMD_CANCEL, $this->txt("cancel"));
        $this->tpl->setContent($form->getHtml());
    }

    protected function editSet(ilPropertyFormGUI $form = null)
    {
        if ($form === null) {
            $form = $this->getForm();
            $this->fillForm($form);
        }
        $form->addCommandButton(self::CMD_UPDATE_SET_SETTINGS, $this->txt("save"));
        $form->addCommandButton(self::CMD_CANCEL, $this->txt("cancel"));
        $this->tpl->setContent($form->getHtml());
    }

    protected function saveSettings()
    {
        $form = $this->getForm();
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->addSet($form);
            return;
        }

        $post = $_POST;
        $this->db->createSet(
            $post[self::F_TITLE],
            (bool) $post[self::F_IS_LOCKED],
            (int) $post[self::F_MIN_SUBMISSIONS]
        );

        ilUtil::sendSuccess($this->txt("create_successful"), true);
        $this->ctrl->redirectByClass(ilSetsGUI::class, ilSetsGUI::CMD_SHOW_SETS);
    }

    protected function updateSettings()
    {
        $form = $this->getForm();
        if (!$this->isSetInUse() && !$form->checkInput()) {
            $form->setValuesByPost();
            $this->editSet($form);
            return;
        }
        $post = $_POST;
        $set = $this->db->selectSetById($this->getValidatedIdFromGet());
        $set = $set->withIsLocked((bool) $post[self::F_IS_LOCKED]);
        if (!$this->isSetInUse()) {
            $set = $set
                ->withTitle($post[self::F_TITLE])
                ->withMinSubmissions((int) $post[self::F_MIN_SUBMISSIONS]);
        }

        $this->db->updateSet($set);

        ilUtil::sendSuccess($this->txt("edit_successful"), true);
        $this->ctrl->setParameter($this, "id", $this->getValidatedIdFromGet());
        $this->ctrl->redirectByClass(ilSetSettingsGUI::class, self::CMD_EDIT_SET);
    }

    protected function cancel()
    {
        $this->ctrl->redirectToURL($this->cancel_link);
    }

    protected function fillForm(ilPropertyFormGUI $form)
    {
        $set = $this->db->selectSetById($this->getValidatedIdFromGet());
        $sets = [
            self::F_TITLE => $set->getTitle(),
            self::F_IS_LOCKED => $set->getIsLocked(),
            self::F_MIN_SUBMISSIONS => $set->getMinSubmissions()
        ];
        $form->setValuesByArray($sets);
    }

    protected function getForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $this->ctrl->setParameter($this, "id", $this->getValidatedIdFromGet());
        $form->setFormAction($this->ctrl->getFormAction($this));
        $this->ctrl->clearParameters($this);
        $form->setTitle($this->txt("set_settings"));
        $form->setShowTopButtons(true);

        $ti = new ilTextInputGUI($this->txt("title"), self::F_TITLE);
        $ti->setRequired(true);
        $ti->setValidationRegexp("/.{3,}/");
        $ti->setValidationFailureMessage($this->txt("lt_three_chars"));
        $ti->setDisabled($this->isSetInUse());
        $form->addItem($ti);

        $ni = new ilNumberInputGUI($this->txt("min_submissions"), self::F_MIN_SUBMISSIONS);
        $ni->setInfo($this->txt("min_submissions_byline"));
        $ni->setRequired(true);
        $ni->setMinValue(0, true);
        $ni->setDisabled($this->isSetInUse());
        $form->addItem($ni);

        $cb = new ilCheckboxInputGUI($this->txt("unavailable"), self::F_IS_LOCKED);
        $form->addItem($cb);

        return $form;
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

    protected function isSetInUse() : bool
    {
        $set = $this->getSet();
        if (!$set) {
            return false;
        }
        return $set->getIsUsed();
    }

    protected function txt(string $code) : string
    {
        $txt = $this->txt;
        return $txt($code);
    }
}

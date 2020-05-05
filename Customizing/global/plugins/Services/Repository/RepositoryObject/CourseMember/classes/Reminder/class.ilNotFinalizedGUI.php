<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Plugins\CourseMember\Reminder\DB;

class ilNotFinalizedGUI
{
    const CMD_SHOW_CONFIG = "showConfig";
    const CMD_SAVE_CONFIG = "saveConfig";

    const INTERVAL = "interval";
    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;

    /**
     * @var Closure
     */
    protected $txt;
    /**
     * @var DB
     */
    protected $db;

    /**
     * @var ilObjUser
     */
    protected $usr;

    public function __construct(ilCtrl $ctrl, ilGlobalTemplateInterface $tpl, Closure $txt, DB $db, ilObjUser $usr)
    {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->txt = $txt;
        $this->db = $db;
        $this->usr = $usr;
    }

    /**
     * @throws Exception if cms is unknown
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_SHOW_CONFIG:
                $this->showConfig();
                break;
            case self::CMD_SAVE_CONFIG:
                $this->saveConfig();
                break;
            default:
                throw new Exception("Unkown command " . $cmd);
        }
    }

    protected function showConfig(ilPropertyFormGUI $form = null)
    {
        if (is_null($form)) {
            $form = $this->initForm();
            $this->fillValues($form);
        }

        $this->tpl->setContent($form->getHTML());
    }

    protected function saveConfig()
    {
        $form = $this->initForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showConfig($form);
            return;
        }

        $post = $_POST;

        $interval = (int) $post[self::INTERVAL];
        $this->db->insert($interval, (int) $this->usr->getId());

        \ilUtil::sendSuccess($this->txt("save_success"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW_CONFIG);
    }

    protected function initForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->txt("not_finalized_title"));
        $form->setFormAction($this->ctrl->getFormAction($this));

        $ni = new ilNumberInputGUI($this->txt("interval"), self::INTERVAL);
        $ni->setInfo($this->txt("interval_info"));
        $ni->setMinValue(0);
        $form->addItem($ni);

        $form->addCommandButton(self::CMD_SAVE_CONFIG, $this->txt("save"));
        $form->addCommandButton(self::CMD_SHOW_CONFIG, $this->txt("cancel"));

        return $form;
    }

    protected function fillValues(ilPropertyFormGUI $form)
    {
        $min_member = $this->db->select();
        $values = [
            self::INTERVAL => $min_member->getInterval()
        ];

        $form->setValuesByArray($values);
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}

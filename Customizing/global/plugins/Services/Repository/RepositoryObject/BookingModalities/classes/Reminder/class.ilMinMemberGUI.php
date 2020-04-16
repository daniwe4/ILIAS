<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Plugins\BookingModalities\Reminder\DB;

class ilMinMemberGUI
{
    const CMD_SHOW_CONFIG = "showConfig";
    const CMD_SAVE_CONFIG = "saveConfig";

    const SEND_MAIL = "send_mail";
    const DAYS_BEFORE_COURSE = "DAYS_BEFORE_COURSE";
    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilTemplate
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

    public function __construct(ilCtrl $ctrl, ilTemplate $tpl, Closure $txt, DB $db, ilObjUser $usr)
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

        $send_mail = false;
        if (isset($post[self::SEND_MAIL]) &&
            $post[self::SEND_MAIL] == 1
        ) {
            $send_mail = true;
        }

        $days_before_course = (int) $post[self::DAYS_BEFORE_COURSE];
        $this->db->insert($send_mail, $days_before_course, (int) $this->usr->getId());

        \ilUtil::sendSuccess($this->txt("save_sucess"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW_CONFIG);
    }

    protected function initForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->txt("min_mem_title"));
        $form->setFormAction($this->ctrl->getFormAction($this));

        $cb = new ilCheckboxInputGUI($this->txt("send_mail"), self::SEND_MAIL);
        $cb->setValue(1);
        $form->addItem($cb);

        $ni = new ilNumberInputGUI($this->txt("days_before_course"), self::DAYS_BEFORE_COURSE);
        $ni->setInfo($this->txt("days_before_course_info"));
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
            self::SEND_MAIL => $min_member->getSendMail(),
            self::DAYS_BEFORE_COURSE => $min_member->getDaysBeforeCourse()
        ];

        $form->setValuesByArray($values);
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}

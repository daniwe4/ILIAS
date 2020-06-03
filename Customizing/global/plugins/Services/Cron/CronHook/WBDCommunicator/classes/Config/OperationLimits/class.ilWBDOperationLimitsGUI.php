<?php

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

declare(strict_types = 1);

use CaT\Plugins\WBDCommunicator\Config\OperationLimits\DB;

class ilWBDOperationLimitsGUI
{
    const CMD_SHOW = 'show';
    const CMD_SAVE = 'save';

    const POST_LIMIT_ANNOUNCEMENTS = 'announcements';
    const POST_START_DATE_ANNOUNCEMENTS = 'start_announcements';
    const POST_LIMIT_REQUESTS = 'requests';
    const POST_LIMIT_CANCELLATION = 'cancel';

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;

    /**
     * @var DB
     */
    protected $db;

    /**
     * @var Closure
     */
    protected $txt;

    public function __construct(
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
        DB $db,
        Closure $txt
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->db = $db;
        $this->txt = $txt;
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();

        switch ($cmd) {
            case self::CMD_SHOW:
                $this->show();
                break;
            case self::CMD_SAVE:
                $this->save();
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    public function show(ilPropertyFormGUI $form = null)
    {
        if (is_null($form)) {
            $form = $this->getForm();
        }

        $values = [
            self::POST_LIMIT_ANNOUNCEMENTS => $this->db->getMaxNumberOfAnnouncemence(),
            self::POST_LIMIT_REQUESTS => $this->db->getLimitForRequest(),
            self::POST_LIMIT_CANCELLATION => $this->db->getMaxNumberOfCancellations()
        ];

        try {
            $date = $this->db->getStartDateForAnnouncement()->format("Y-m-d");
        } catch (LogicException $e) {
            $date = null;
        }
        $values[self::POST_START_DATE_ANNOUNCEMENTS] = $date;

        $form->setValuesByArray($values);
        $this->tpl->setContent($form->getHTML());
    }

    public function save()
    {
        $form = $this->getForm();
        $form->setValuesByPost();
        if (!$form->checkInput()) {
            $this->show($form);
            return;
        }

        $this->db->setMaxNumberOfAnnouncemence(
            (int) $form->getItemByPostVar(self::POST_LIMIT_ANNOUNCEMENTS)->getValue()
        );
        $this->db->setLimitForRequest(
            (int) $form->getItemByPostVar(self::POST_LIMIT_REQUESTS)->getValue()
        );
        $this->db->setMaxNumberOfCancellations(
            (int) $form->getItemByPostVar(self::POST_LIMIT_CANCELLATION)->getValue()
        );

        $date = $form->getItemByPostVar(self::POST_START_DATE_ANNOUNCEMENTS)->getDate();
        if (!is_null($date)) {
            $this->db->setStartDateForAnnouncement(DateTime::createFromFormat("Y-m-d", $date->get(IL_CAL_DATE)));
        }

        ilUtil::sendSuccess($this->txt("op_limits_saved"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW);
    }

    protected function getForm()
    {
        $form = new \ilPropertyFormGUI();
        $form->setTitle($this->txt('operation_limit_settings'));
        $form->setFormAction($this->ctrl->getFormAction($this));

        $sub_header = new ilFormSectionHeaderGUI();
        $sub_header->setTitle($this->txt('announcements'));
        $form->addItem($sub_header);
        $announcements = new ilNumberInputGUI(
            $this->txt('limit_announcements'),
            self::POST_LIMIT_ANNOUNCEMENTS
        );
        $announcements->setMinValue(0);
        $announcements->allowDecimals(false);
        $announcements->setRequired(false);
        $form->addItem($announcements);

        $announcements_start_date = new ilDateTimeInputGUI(
            $this->txt('announcements_start_date'),
            self::POST_START_DATE_ANNOUNCEMENTS
        );
        $announcements_start_date->setInfo($this->txt('announcements_start_date_info'));
        $form->addItem($announcements_start_date);

        $sub_header = new ilFormSectionHeaderGUI();
        $sub_header->setTitle($this->txt('requests'));
        $form->addItem($sub_header);
        $requests = new ilNumberInputGUI(
            $this->txt('limit_requests'),
            self::POST_LIMIT_REQUESTS
        );
        $requests->setMinValue(0);
        $requests->allowDecimals(false);
        $requests->setRequired(false);
        $form->addItem($requests);

        $sub_header = new ilFormSectionHeaderGUI();
        $sub_header->setTitle($this->txt('cancellations'));
        $form->addItem($sub_header);
        $cancellation = new ilNumberInputGUI(
            $this->txt('limit_cancellations'),
            self::POST_LIMIT_CANCELLATION
        );
        $cancellation->setMinValue(0);
        $cancellation->allowDecimals(false);
        $cancellation->setRequired(false);
        $form->addItem($cancellation);

        $form->addCommandButton(self::CMD_SAVE, $this->txt('save'));
        return $form;
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}

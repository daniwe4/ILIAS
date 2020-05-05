<?php

declare(strict_types=1);

use CaT\Plugins\EduBiography\Config\OverviewCertificate\Schedules;

class ilScheduleGUI
{
    const CMD_CREATE_SCHEDULE = "createSchedule";
    const CMD_EDIT_SCHEDULE = "editSchedule";
    const CMD_SAVE_SCHEDULE = "saveSchedule";
    const CMD_DELETE_SCHEDULE = "deleteSchedule";
    const CMD_DELETE_CONFIRM = "deleteConfirm";
    const CMD_CANCEL = "cancel";

    const F_ID = "f_id";
    const F_TITLE = "f_title";
    const F_SCHEDULE = "f_schedule";
    const F_MIN_IDD_VALUE = "f_min_idd_value";
    const F_PARTICIPATIONS_DOCUMENT_ACTIVE = "f_participations_document_active";

    const NEW_VALUE = -1;

    const URL_PARAM = "schedule_id";

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;

    /**
     * @var string
     */
    protected $parent_link;

    /**
     * @var Schedules\DB
     */
    protected $db;

    /**
     * @var Closure
     */
    protected $txt;

    /**
     * @var string
     */
    protected $directory;

    public function __construct(
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
        string $parent_link,
        Schedules\DB $db,
        Closure $txt
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->parent_link = $parent_link;
        $this->db = $db;
        $this->txt = $txt;
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_CREATE_SCHEDULE:
                $this->createSchedule();
                break;
            case self::CMD_EDIT_SCHEDULE:
                $this->editSchedule();
                break;
            case self::CMD_SAVE_SCHEDULE:
                $this->saveSchedule();
                break;
            case self::CMD_DELETE_SCHEDULE:
                $this->deleteSchedule();
                break;
            case self::CMD_CANCEL:
                $this->redirectParent();
                break;
            case self::CMD_DELETE_CONFIRM:
                $this->deleteConfirm();
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    protected function createSchedule(ilPropertyFormGUI $form = null)
    {
        if (is_null($form)) {
            $form = $this->initForm();
            $this->setCreateValues($form);
        }

        $form->addCommandButton(self::CMD_SAVE_SCHEDULE, $this->txt("save"));
        $form->addCommandButton(self::CMD_CANCEL, $this->txt("cancel"));

        $this->tpl->setContent($form->getHTML());
    }

    protected function editSchedule(ilPropertyFormGUI $form = null)
    {
        if (is_null($form)) {
            $id = (int) $_GET[self::URL_PARAM];
            $form = $this->initForm();
            $this->setCurrentValues($form, $id);
        }

        $form->addCommandButton(self::CMD_SAVE_SCHEDULE, $this->txt("save"));
        $form->addCommandButton(self::CMD_CANCEL, $this->txt("cancel"));

        $this->tpl->setContent($form->getHTML());
    }

    protected function saveSchedule()
    {
        $form = $this->initForm();

        $form->setValuesByPost();

        $id = $form->getItemByPostVar(self::F_ID)->getValue();
        if ($id == self::NEW_VALUE) {
            $this->saveNewSchedule($form);
        } else {
            $this->updateSchedule($form);
        }
    }

    protected function saveNewSchedule(ilPropertyFormGUI $form)
    {
        if (!$form->checkInput()) {
            $this->createSchedule($form);
            return;
        }

        $title = $form->getItemByPostVar(self::F_TITLE)->getValue();

        if ($this->db->isTitleInUse($title)) {
            ilUtil::sendFailure($this->txt('title_is_in_use'));
            $this->createSchedule($form);
            return;
        }

        /** @var ilDateDurationInputGUI $schedule */
        $schedule = $form->getItemByPostVar(self::F_SCHEDULE);
        $start = DateTime::createFromFormat("Y-m-d", $schedule->getStart()->get(IL_CAL_DATE));
        $end = DateTime::createFromFormat("Y-m-d", $schedule->getEnd()->get(IL_CAL_DATE));

        /** @var ilTimeInputGUI $time */
        $time = $form->getItemByPostVar(self::F_MIN_IDD_VALUE);
        $hours = $time->getHours();
        $minutes = $time->getMinutes();
        $min_idd_value = $hours * 60 + $minutes;

        /** @var ilCheckboxInputGUI $ci */
        $ci = $form->getItemByPostVar(self::F_PARTICIPATIONS_DOCUMENT_ACTIVE);
        $part_document = (bool) $ci->getChecked();

        $this->db->create(
            $title,
            $start,
            $end,
            $min_idd_value,
            $part_document
        );

        ilUtil::sendSuccess($this->txt("schedule_created"), true);
        $this->redirectParent();
    }

    protected function updateSchedule(ilPropertyFormGUI $form)
    {
        if (!$form->checkInput()) {
            $this->editSchedule($form);
            return;
        }

        $id = (int) $form->getItemByPostVar(self::F_ID)->getValue();
        $schedule = $this->db->selectFor($id);
        $old_title = $schedule->getTitle();
        $title = $form->getItemByPostVar(self::F_TITLE)->getValue();

        if ($old_title !== $title) {
            if ($this->db->isTitleInUse($title)) {
                ilUtil::sendFailure($this->txt('title_is_in_use'));
                $this->createSchedule($form);
                return;
            }
        }

        /** @var ilDateDurationInputGUI $schedule */
        $new_schedule = $form->getItemByPostVar(self::F_SCHEDULE);
        $start = DateTime::createFromFormat("Y-m-d", $new_schedule->getStart()->get(IL_CAL_DATE));
        $end = DateTime::createFromFormat("Y-m-d", $new_schedule->getEnd()->get(IL_CAL_DATE));

        /** @var ilTimeInputGUI $time */
        $time = $form->getItemByPostVar(self::F_MIN_IDD_VALUE);
        $hours = $time->getHours();
        $minutes = $time->getMinutes();
        $min_idd_value = $hours * 60 + $minutes;

        /** @var ilCheckboxInputGUI $ci */
        $ci = $form->getItemByPostVar(self::F_PARTICIPATIONS_DOCUMENT_ACTIVE);
        $part_document = (bool) $ci->getChecked();

        $schedule = $schedule->withTitle($title)
            ->withStart($start)
            ->withEnd($end)
            ->withMinIddValue($min_idd_value)
            ->withParticipationsDocumentActive($part_document)
        ;

        $this->db->update($schedule);

        ilUtil::sendSuccess($this->txt("schedule_updated"), true);
        $this->redirectParent();
    }

    protected function deleteConfirm()
    {
        $id = (int) $_GET[self::URL_PARAM];
        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormAction($this));
        $confirm->setHeaderText($this->txt("should_delete_schedule"));

        $confirm->addHiddenItem(self::URL_PARAM, $id);

        $confirm->setConfirm($this->txt("delete"), self::CMD_DELETE_SCHEDULE);
        $confirm->setCancel($this->txt("cancel"), self::CMD_CANCEL);

        $this->tpl->setContent($confirm->getHTML());
    }

    protected function deleteSchedule()
    {
        $id = (int) $_POST[self::URL_PARAM];
        $this->db->deleteFor($id);

        ilUtil::sendSuccess($this->txt("schedule_deleted"), true);
        $this->redirectParent();
    }

    protected function redirectParent()
    {
        $this->ctrl->redirectToURL($this->parent_link);
    }

    protected function initForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->txt("new_schedule"));
        $form->setFormAction($this->ctrl->getFormAction($this));

        $hi = new ilHiddenInputGUI(self::F_ID);
        $form->addItem($hi);

        $ti = new ilTextInputGUI($this->txt("title"), self::F_TITLE);
        $ti->setRequired(true);
        $form->addItem($ti);

        $dur = new ilDateDurationInputGUI($this->txt("schedule"), self::F_SCHEDULE);
        $dur->setRequired(true);
        $form->addItem($dur);

        $ci = new ilCheckboxInputGUI($this->txt("participation_document"), self::F_PARTICIPATIONS_DOCUMENT_ACTIVE);
        $ci->setValue(1);
        $form->addItem($ci);

        $time = new \ilTimeInputGUI($this->txt("min_idd_value"), self::F_MIN_IDD_VALUE);
        $time->setMaxHours(250);
        $time->setMinuteStepSize(5);
        $time->setRequired(true);
        $form->addItem($time);

        return $form;
    }

    protected function setCreateValues(ilPropertyFormGUI $form)
    {
        $value = [
            self::F_ID => self::NEW_VALUE
        ];

        $form->setValuesByArray($value);
    }

    protected function setCurrentValues(ilPropertyFormGUI $form, int $id)
    {
        $schedule = $this->db->selectFor($id);

        $timings = [
            "start" => $schedule->getStart()->format("Y-m-d"),
            "end" => $schedule->getEnd()->format("Y-m-d")
        ];

        $hh = floor($schedule->getMinIddValue() / 60);
        $mm = $schedule->getMinIddValue() - ($hh * 60);

        $value = [
            self::F_ID => $id,
            self::F_TITLE => $schedule->getTitle(),
            self::F_SCHEDULE => $timings,
            self::F_MIN_IDD_VALUE => [
                "hh" => $hh,
                "mm" => $mm
            ],
            self::F_PARTICIPATIONS_DOCUMENT_ACTIVE => $schedule->isParticipationsDocumentActive()
        ];

        $form->setValuesByArray($value);
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}

<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */
/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

class ilAgendaSettingsGUI
{
    const CMD_EDIT_PROPERTIES = "editProperties";
    const CMD_SAVE = "saveProperties";

    const F_TITLE = "f_title";
    const F_DESCRIPTION = "f_description";
    const F_START_TIME = "f_start_time";

    /**
     * @var ilObjAgendaGUI
     */
    protected $parent;

    /**
     * @var ilObjAgenda
     */
    protected $actions;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    public function __construct(
        ilObjAgenda $object,
        ilTemplate $tpl,
        ilCtrl $ctrl
    ) {
        $this->object = $object;
        $this->tpl = $tpl;
        $this->ctrl = $ctrl;
    }

    /**
     * @throws Exception
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();

        switch ($cmd) {
            case self::CMD_EDIT_PROPERTIES:
                $this->editProperties();
                break;
            case self::CMD_SAVE:
                $this->saveProperties();
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    /**
     * Show the current settings
     */
    protected function editProperties(ilPropertyFormGUI $form = null)
    {
        if ($form === null) {
            $form = $this->initForm();
            $this->fillForm($form);
        }

        $form->addCommandButton(self::CMD_SAVE, $this->txt("save"));
        $form->addCommandButton(self::CMD_EDIT_PROPERTIES, $this->txt("cancel"));

        $this->tpl->setContent($form->getHtml());
    }

    /**
     * Save the settings
     */
    protected function saveProperties()
    {
        $form = $this->initForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->editProperties($form);
            return;
        }

        $post = $_POST;
        $title = $post[self::F_TITLE];
        $description = $post[self::F_DESCRIPTION];
        $start_time = $post[self::F_START_TIME];

        $this->object->setTitle($title);
        $this->object->setDescription($description);

        $start_time = new DateTime(
            $start_time["hh"] . ":" . $start_time["mm"],
            new DateTimeZone("Europe/Berlin")
        );

        $fnc = function ($s) use ($start_time) {
            return $s->withStartTime($start_time);
        };

        $this->object->updateSettings($fnc);
        $this->object->update();
        $this->updateSession();

        ilUtil::sendSuccess($this->txt("settings_saved"), true);
        $this->ctrl->redirect($this, self::CMD_EDIT_PROPERTIES);
    }

    protected function initForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->txt("settings"));
        $form->setFormAction($this->ctrl->getFormAction($this));

        $ti = new ilTextInputGUI($this->txt("title"), self::F_TITLE);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ta = new ilTextareaInputGUI($this->txt("description"), self::F_DESCRIPTION);
        $form->addItem($ta);

        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->txt("settings_start"));
        $form->addItem($sh);

        $ti = new ilTimeInputGUI($this->txt("agenda_start_time"), self::F_START_TIME);
        $ti->setRequired(true);
        $form->addItem($ti);

        return $form;
    }

    protected function fillForm(ilPropertyFormGUI $form)
    {
        $values = array();

        $values[self::F_TITLE] = $this->object->getTitle();
        $values[self::F_DESCRIPTION] = $this->object->getDescription();

        $start_time = $this->object->getSettings()->getStartTime();

        if (!is_null($start_time)) {
            $values[self::F_START_TIME]["hh"] = $start_time->format("H");
            $values[self::F_START_TIME]["mm"] = $start_time->format("i");
        }

        $form->setValuesByArray($values);
    }

    protected function txt(string $code) : string
    {
        return $this->object->pluginTxt($code);
    }

    /**
     * Updates the session where this agenda is assigned to
     */
    protected function updateSession()
    {
        $this->object->updateSession();
    }
}

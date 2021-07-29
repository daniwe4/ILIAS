<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

class ilObjLearningSequenceSettingsPresentationGUI
{
    const CMD_SAVE = "update";
    const CMD_CANCEL = "cancel";
    const CMD_SETTINGS_PRESENTATION = "settingsPresentation";

    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilObjectService $obj_service;

    public function __construct(
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
        ilLanguage $lng,
        ilObjLearningSequence $obj,
        ilObjectService $obj_service
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->obj = $obj;
        $this->obj_service = $obj_service;

        $this->lng->loadLanguageModule('content');
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();

        switch ($cmd) {
            case self::CMD_SETTINGS_PRESENTATION:
            case self::CMD_SAVE:
            case self::CMD_CANCEL:
                $this->$cmd();
                break;
            default:
                throw new ilException("ilObjLearningSequenceSettingsGUI: Command not supported: $cmd");
        }
    }

    protected function settingsPresentation() : void
    {
        $form = $this->buildForm();
        $this->addCommonFieldsToForm($form);
        $this->tpl->setContent($form->getHTML());
    }

    protected function cancel() : void
    {
        $this->ctrl->redirectByClass(self::class, self::CMD_SETTINGS_PRESENTATION);
    }

    protected function update() : void
    {
        $form = $this->buildForm();
        $this->addCommonFieldsToForm($form);

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->tpl->setOnScreenMessage("failure", $this->lng->txt("msg_form_save_error"));
            $this->tpl->setContent($form->getHTML());
            return;
        }

        $form_service = $this->obj_service->commonSettings()->legacyForm($form, $this->obj);
        $form_service->saveTitleIconVisibility();
        $form_service->saveTopActionsVisibility();
        $form_service->saveIcon();
        $form_service->saveTileImage();

        $this->tpl->setOnScreenMessage("success", $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirectByClass(self::class, self::CMD_SETTINGS_PRESENTATION);
    }

    protected function buildForm() : ilPropertyFormGUI
    {
        $txt = function ($id) {
            return $this->lng->txt($id);
        };

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, self::CMD_SAVE));

        $form->addCommandButton(self::CMD_SAVE, $txt("save"));
        $form->addCommandButton(self::CMD_CANCEL, $txt("cancel"));

        return $form;
    }

    protected function addCommonFieldsToForm(\ilPropertyFormGUI $form) : void
    {
        $txt = function ($id) {
            return $this->lng->txt($id);
        };
        $section_appearance = new ilFormSectionHeaderGUI();
        $section_appearance->setTitle($txt('cont_presentation'));
        $form->addItem($section_appearance);
        $form_service = $this->obj_service->commonSettings()->legacyForm($form, $this->obj);
        $form_service->addTitleIconVisibility();
        $form_service->addTopActionsVisibility();
        $form_service->addIcon();
        $form_service->addTileImage();
    }
}

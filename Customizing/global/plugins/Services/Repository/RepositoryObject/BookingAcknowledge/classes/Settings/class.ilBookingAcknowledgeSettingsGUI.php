<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Plugins\BookingAcknowledge\BookingAcknowledge;
use CaT\Plugins\BookingAcknowledge\Settings;

class ilBookingAcknowledgeSettingsGUI
{
    const CMD_EDIT_PROPERTIES = "editProperties";
    const CMD_SAVE_PROPERTIES = "saveProperties";

    const F_TITLE = "f_title";
    const F_DESCRIPTION = "f_description";
    const F_IS_ONLINE = "f_is_online";

    /**
     * @var \ilCtrl
     */
    protected $ctrl;

    /**
     * @var \ilGlobalTemplateInterface
     */
    protected $tpl;

    /**
     * @var \Closure
     */
    protected $txt_closure;

    /**
     * @var BookingAcknowledge
     */
    protected $object;

    public function __construct(
        \ilCtrl $ctrl,
        \ilGlobalTemplateInterface $tpl,
        BookingAcknowledge $object
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->txt_closure = $object->getTxtClosure();
        $this->object = $object;
    }

    /**
     * @throws Exception if cmd is unknown
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_EDIT_PROPERTIES:
                $this->showSettingsForm();
                break;
            case self::CMD_SAVE_PROPERTIES:
                $this->saveSettings();
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    protected function showSettingsForm(ilPropertyFormGUI $form = null)
    {
        if (is_null($form)) {
            $form = $this->initForm();
            $this->fillForm($form);
        }

        $this->tpl->setContent($form->getHtml());
    }

    protected function saveSettings()
    {
        $form = $this->initForm();
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showSettingsForm($form);
            return;
        }

        $post = $_POST;
        $title = $post[self::F_TITLE];
        $description = $post[self::F_DESCRIPTION];

        $this->object->setTitle($title);
        $this->object->setDescription($description);
        $this->object->update();

        $this->ctrl->redirect($this, self::CMD_EDIT_PROPERTIES);
    }

    protected function initForm() : \ilPropertyFormGUI
    {
        $form = new \ilPropertyFormGUI();
        $form->setTitle($this->txt("title"));
        $form->setFormAction($this->ctrl->getFormAction($this));

        $ti = new \ilTextInputGUI($this->txt("title"), self::F_TITLE);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ta = new \ilTextAreaInputGUI($this->txt("description"), self::F_DESCRIPTION);
        $form->addItem($ta);

        $form->addCommandButton(self::CMD_SAVE_PROPERTIES, $this->txt("save"));
        $form->addCommandButton(self::CMD_EDIT_PROPERTIES, $this->txt("cancel"));

        return $form;
    }

    protected function fillForm(\ilPropertyFormGUI $form)
    {
        $values = $this->getFormValues();
        $form->setValuesByArray($values);
    }

    /**
     * @return <int, string|bool>
     */
    protected function getFormValues() : array
    {
        $values = [];
        $values[self::F_TITLE] = $this->object->getTitle();
        $values[self::F_DESCRIPTION] = $this->object->getDescription();

        return $values;
    }

    protected function txt(string $code) : string
    {
        $txt = $this->txt_closure;
        return $txt($code);
    }
}

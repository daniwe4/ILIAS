<?php

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

declare(strict_types = 1);

use CaT\Plugins\WBDCommunicator\Config\Tgic\DB;
use CaT\Plugins\WBDCommunicator\Config\Tgic\FileStorage;

class ilTgicGUI
{
    const CMD_SHOW = 'show';
    const CMD_SAVE = 'save';

    const PARTNER_ID = "partner_id";
    const CERTSTORE = "certstore";
    const PASSWORD = "password";

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
     * @var FileStorage
     */
    protected $file_storage;

    /**
     * @var Closure
     */
    protected $txt;

    public function __construct(
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
        DB $db,
        FileStorage $file_storage,
        Closure $txt
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->db = $db;
        $this->file_storage = $file_storage;
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
            $this->fillForm($form);
        }

        $this->tpl->setContent($form->getHTML());
    }

    public function save()
    {
        $form = $this->getForm();
        $form->setValuesByPost();
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->show($form);
            return;
        }

        $post = $_POST;
        $file_info = $_FILES[self::CERTSTORE];

        $file_path = $this->file_storage->uploadFile($file_info);
        if (is_null($file_path)) {
            \ilUtil::sendFailure("file_could_not_be_uploaded");
            $form->setValuesByPost();
            $this->show($form);
            return;
        }

        $this->db->saveTgicSettings(
            $post[self::PARTNER_ID],
            $file_path,
            $post[self::PASSWORD]
        );

        ilUtil::sendSuccess($this->txt("tgic_saved"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW);
    }

    protected function getForm()
    {
        $form = new \ilPropertyFormGUI();
        $form->setTitle($this->txt('tgic_settings'));
        $form->setFormAction($this->ctrl->getFormAction($this));

        $ti = new ilTextInputGUI($this->txt('partner_id'), self::PARTNER_ID);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ti = new ilTextInputGUI($this->txt('password'), self::PASSWORD);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ti = new ilFileInputGUI($this->txt('certstore'), self::CERTSTORE);
        $ti->setRequired(true);
        $form->addItem($ti);

        $form->addCommandButton(self::CMD_SAVE, $this->txt('save'));
        return $form;
    }

    protected function fillForm(ilPropertyFormGUI $form)
    {
        try {
            $settings = $this->db->getTgicSettings();
            $certstore = $settings->getCertstore();
            $file_name = substr(
                $certstore,
                strrpos(
                    $certstore,
                    "/"
                ) + 1,
                strlen($certstore)
            );

            $vals = [
                self::PARTNER_ID => $settings->getPartnerId(),
                self::CERTSTORE => $file_name,
                self::PASSWORD => $settings->getPassword()
            ];

            if (
                $this->file_storage->isEmpty() ||
                !$this->file_storage->fileExists($settings->getCertstore())
            ) {
                $vals[self::CERTSTORE] = null;
            }
        } catch (LogicException $e) {
            $vals = [];
        }

        $form->setValuesByArray($vals);
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}

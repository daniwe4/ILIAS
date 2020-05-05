<?php

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

declare(strict_types = 1);

use CaT\Plugins\WBDCommunicator\Config\Connection;

class ilWBDConnectionGUI
{
    const CMD_SHOW = "showConnectionSettings";
    const CMD_SAVE = "saveConnectionSettings";

    const HOST = "f_host";
    const PORT = "f_port";
    const ENDPOINT = "f_endpoint";
    const NAMESPACE = "f_namespace";
    const NAME = "f_name";

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;

    /**
     * @var Connection\DB
     */
    protected $db;

    /**
     * @var Closure
     */
    protected $txt;

    public function __construct(
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
        Connection\DB $db,
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
                $this->showConnectionSettings();
                break;
            case self::CMD_SAVE:
                $this->saveConnectionSettings();
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    protected function showConnectionSettings(ilPropertyFormGUI $form = null)
    {
        if (is_null($form)) {
            $form = $this->initForm();
            $this->fillForm($form);
        }

        $this->tpl->setContent($form->getHTML());
    }

    protected function saveConnectionSettings()
    {
        $form = $this->initForm();
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showConnectionSettings($form);
            return;
        }

        $post = $_POST;
        $settings = $this->db->getConnection();
        $settings = $settings->withHost($post[self::HOST])
            ->withPort($post[self::PORT])
            ->withEndpoint($post[self::ENDPOINT])
            ->withNamespace($post[self::NAMESPACE])
            ->withName($post[self::NAME])
        ;

        $this->db->saveConnection($settings);

        ilUtil::sendSuccess($this->txt("connection_settings_saved"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW);
    }

    protected function initForm()
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->txt("connecton_settings"));
        $form->setFormAction($this->ctrl->getFormAction($this));

        $ti = new ilTextInputGUI($this->txt('form_proxy_host'), self::HOST);
        $form->addItem($ti);

        $ti = new ilTextInputGUI($this->txt('form_proxy_port'), self::PORT);
        $form->addItem($ti);

        $ti = new ilTextInputGUI($this->txt('form_endpoint'), self::ENDPOINT);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ti = new ilTextInputGUI($this->txt('form_namespace'), self::NAMESPACE);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ti = new ilTextInputGUI($this->txt('form_name'), self::NAME);
        $ti->setRequired(true);
        $form->addItem($ti);

        $form->addCommandButton(self::CMD_SAVE, $this->txt("save"));

        return $form;
    }

    protected function fillForm(ilPropertyFormGUI $form)
    {
        $settings = $this->db->getConnection();
        $values = [
            self::HOST => $settings->getHost(),
            self::PORT => $settings->getPort(),
            self::ENDPOINT => $settings->getEndpoint(),
            self::NAMESPACE => $settings->getNamespace(),
            self::NAME => $settings->getName()
        ];
        $form->setValuesByArray($values);
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}

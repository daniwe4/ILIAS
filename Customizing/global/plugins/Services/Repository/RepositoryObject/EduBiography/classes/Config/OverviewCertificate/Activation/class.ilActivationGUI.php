<?php

declare(strict_types=1);

use CaT\Plugins\EduBiography\Config\OverviewCertificate\Activation;

class ilActivationGUI
{
    const CMD_SAVE = "saveConfig";
    const CMD_SHOW = "showConfig";
    const F_ACTIVE = "active";

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var Closure
     */
    protected $txt;

    /**
     * @var Activation\DB
     */
    protected $db;

    public function __construct(
        ilCtrl $ctrl,
        ilTemplate $tpl,
        Closure $txt,
        ilObjUser $user,
        Activation\DB $db
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->txt = $txt;
        $this->user = $user;
        $this->db = $db;
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_SAVE:
                $this->saveConfig();
                break;
            case self::CMD_SHOW:
                $this->showConfig();
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    protected function showConfig(ilPropertyFormGUI $form = null)
    {
        if (is_null($form)) {
            $form = $this->buildForm();
            $this->fillForm($form);
        }

        $this->tpl->setContent($form->getHTML());
    }

    protected function saveConfig()
    {
        $form = $this->buildForm();
        $form->setValuesByPost();
        if (!$form->checkInput()) {
            $this->showConfig($form);
            return;
        }

        /** @var ilCheckboxInputGUI $active */
        $active = $form->getItemByPostVar(self::F_ACTIVE);

        $this->db->insert(
            (bool) $active->getChecked(),
            (int) $this->user->getId(),
            new DateTime()
        );

        ilUtil::sendSuccess($this->txt("config_saved"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW);
    }

    protected function buildForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->txt("config_title"));
        $form->setFormAction($this->ctrl->getFormAction($this));

        $cb = new ilCheckboxInputGUI($this->txt("visible"), self::F_ACTIVE);
        $cb->setValue(1);
        $form->addItem($cb);

        $form->addCommandButton(self::CMD_SAVE, $this->txt("save"));
        $form->addCommandButton(self::CMD_SHOW, $this->txt("cancel"));

        return $form;
    }

    protected function fillForm(ilPropertyFormGUI $form)
    {
        $config = $this->db->select();
        $values = [
            self::F_ACTIVE => $config->isActive()
        ];
        $form->setValuesByArray($values);
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}

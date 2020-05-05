<?php

declare(strict_types = 1);

use CaT\Plugins\WBDCommunicator\Config\WBD;

class ilSystemConfigurationGUI
{
    const CMD_SHOW = "show";
    const CMD_SAVE = "save";

    const WBD_SYSTEM = "wbd_system";

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;

    /**
     * @var WBD\DB
     */
    protected $db;

    /**
     * @var Closure
     */
    protected $txt;

    public function __construct(
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
        WBD\DB $db,
        Closure $txt
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->db = $db;
        $this->txt = $txt;
    }

    /**
     * @throws Exception if command is not known
     */
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
            $form = $this->buildForm();
            $this->setValues($form);
        }

        $this->tpl->setContent($form->getHTML());
    }

    protected function setValues(ilPropertyFormGUI $form)
    {
        $system = $this->db->getActiveWBDSystem();
        $values = [
            self::WBD_SYSTEM => $system->getName()
        ];

        $form->setValuesByArray($values);
    }

    protected function buildForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->txt('system_configuration'));
        $form->setFormAction($this->ctrl->getFormAction($this));

        $rg = new ilRadioGroupInputGUI($this->txt("wbd_systems"), self::WBD_SYSTEM);
        $rg->setRequired(true);
        $op = new ilRadioOption($this->txt("wbd_live"), WBD\System::WBD_LIVE);
        $rg->addOption($op);

        $op = new ilRadioOption($this->txt("wbd_test"), WBD\System::WBD_TEST);
        $rg->addOption($op);

        $form->addItem($rg);

        $form->addCommandButton(self::CMD_SAVE, $this->txt('save'));

        return $form;
    }

    public function save()
    {
        $form = $this->buildForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->show($form);
            return;
        }

        $post = $_POST;
        $wbd_system = $post[self::WBD_SYSTEM];
        $this->db->saveActiveWBDSystem($wbd_system);

        ilUtil::sendSuccess($this->txt("wbd_system_saved"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW);
    }

    protected function txt(string $code) : string
    {
        $txt = $this->txt;
        return $txt($code);
    }
}

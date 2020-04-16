<?php

declare(strict_types=1);

use CaT\Plugins\Webinar;
use CaT\Plugins\Webinar\Config\Config;

/**
 * GUI class to configure the telefon source for the plugin.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE
 */
class ilConfigWebinarGUI
{
    const CMD_SHOW_ENTRIES = "showEntries";
    const CMD_SAVE_CONFIG = "saveConfig";
    const CMD_CANCEL = "cancel";

    const F_SRC_TELEFON = "src_phone";

    const F_PHONE_WORK = "phone_office";
    const F_PHONE_PRIVATE = "phone_home";
    const F_PHONE_MOBIL = "phone_mobile";

    public function __construct(
        \ilCtrl $ctrl,
        \ilTemplate $tpl,
        \Closure $txt,
        Config $config
    ) {
        global $DIC;

        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->txt = $txt;
        $this->config = $config;
    }

    /**
     * Delegate incomming commands to methods.
     *
     * @throws Exception
     * @return void
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();

        switch ($cmd) {
            case self::CMD_SAVE_CONFIG:
                $this->saveConfig();
                break;
            case self::CMD_CANCEL:
            case self::CMD_SHOW_ENTRIES:
                $this->showEntries();
                break;
            default:
                throw new \Exception(__METHOD__ . ": unknown command: " . $cmd);
        }
    }

    public function showEntries(ilPropertyFormGUI $form = null)
    {
        if (is_null($form)) {
            $form = $this->getForm();
            $this->setValue($form);
        }
        $this->tpl->setContent($form->getHtml());
    }

    public function saveConfig()
    {
        $src = $_POST['src_phone'];
        if ($src === null || $src === "") {
            $src = "";
        }

        $this->config->setPhoneType($src);
        $this->ctrl->redirect($this, self::CMD_SHOW_ENTRIES);
    }

    protected function getForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->txt("phone_config"));
        $form->setShowTopButtons(true);
        $form->addCommandButton(self::CMD_SAVE_CONFIG, $this->txt("save"));
        $form->addCommandButton(self::CMD_CANCEL, $this->txt("cancel"));

        $si = new ilSelectInputGUI($this->txt("select_phone_src"), self::F_SRC_TELEFON);
        $options = array(null => $this->txt("please_select")) + $this->getTelefonSources();
        $si->setOptions($options);
        $si->setRequired(true);
        $form->addItem($si);

        return $form;
    }

    protected function setValue(ilPropertyFormGUI $form)
    {
        $values = [
            self::F_SRC_TELEFON => $this->config->getPhoneType()
        ];

        $form->setValuesByArray($values);
    }

    /**
     * @return string[]
     */
    protected function getTelefonSources() : array
    {
        return array(
            self::F_PHONE_WORK => $this->txt(self::F_PHONE_WORK),
            self::F_PHONE_PRIVATE => $this->txt(self::F_PHONE_PRIVATE),
            self::F_PHONE_MOBIL => $this->txt(self::F_PHONE_MOBIL)
        );
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}

<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Plugins\Agenda\Config\Blocks\DB;

class ilBlocksGUI
{
    const CMD_SAVE_CONFIG = "saveConfig";
    const CMD_SHOW_CONFIG = "showConfig";

    const F_EDIT_FIXED_BLOCKS = "f_edit_fixed_blocks";

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

    /**
     * @throws Exception if command is not known
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();

        switch ($cmd) {
            case self::CMD_SHOW_CONFIG:
                $this->showConfig();
                break;
            case self::CMD_SAVE_CONFIG:
                $this->saveConfig();
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    protected function showConfig()
    {
        $form = $this->initForm();
        $this->fillForm($form);
        $this->tpl->setContent($form->getHTML());
    }

    protected function saveConfig()
    {
        $post = $_POST;
        $edit_blocks = false;
        if (
            array_key_exists(self::F_EDIT_FIXED_BLOCKS, $post) &&
            $post[self::F_EDIT_FIXED_BLOCKS] == 1
        ) {
            $edit_blocks = true;
        }
        $this->db->saveBlockConfig($edit_blocks);

        ilUtil::sendSuccess($this->txt("blocks_config_saved"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW_CONFIG);
    }

    protected function initForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->txt("blocks_settings"));
        $form->addCommandButton(self::CMD_SAVE_CONFIG, $this->txt("save"));

        $cb = new ilCheckboxInputGUI($this->txt("edit_fixed_blocks"), self::F_EDIT_FIXED_BLOCKS);
        $cb->setInfo($this->txt("edit_fixed_blocks_info"));
        $cb->setValue(1);
        $form->addItem($cb);

        return $form;
    }

    protected function fillForm(ilPropertyFormGUI $form)
    {
        $block_config = $this->db->selectBlockConfig();
        $values = [
            self::F_EDIT_FIXED_BLOCKS => $block_config->isEditFixedBlocks()
        ];
        $form->setValuesByArray($values);
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}

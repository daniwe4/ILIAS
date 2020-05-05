<?php declare(strict_types=1);

use CaT\Plugins\ParticipationsImport\Filesystem\ConfigStorage;
use CaT\Plugins\ParticipationsImport\Filesystem\Config;

class ilPIFilesystemConfiguratorGUI
{
    protected $plugin;
    protected $ctrl;
    protected $tpl;
    protected $tabs;
    protected $cs;

    const CMD_SHOW = 'show';
    const CMD_SAVE = 'save';

    const POST_PATH = 'path';
    const POST_FILETITLE_TEMPLATE = 'filetitle_template';

    public function __construct(
        \ilParticipationsImportPlugin $plugin,
        \ilCtrl $ctrl,
        \ilGlobalTemplateInterface $tpl,
        \ilTabsGUI $tabs,
        ConfigStorage $cs
    ) {
        $this->plugin = $plugin;
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->tabs = $tabs;
        $this->cs = $cs;
    }

    protected function txt($var)
    {
        return $this->plugin->txt($var);
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
                throw new ilException("no command or next class given");
        }
        return true;
    }

    protected function show()
    {
        $form = $this->getForm();
        $config = $this->cs->loadCurrentConfig();
        $form->getItemByPostVar(self::POST_PATH)
            ->setValue($config->path());
        $form->getItemByPostVar(self::POST_FILETITLE_TEMPLATE)
            ->setValue($config->filetitleTemplate());
        $this->tpl->setContent($form->getHTML());
    }

    protected function save()
    {
        $form = $this->getForm();
        $form->setValuesByPost();
        if ($form->checkInput()) {
            $this->cs->storeConfigAsCurrent(
                new Config(
                    (string) $form->getItemByPostVar(self::POST_PATH)->getValue(),
                    (string) $form->getItemByPostVar(self::POST_FILETITLE_TEMPLATE)->getValue()
                )
            );
            $this->show();
        } else {
            $this->tpl->setContent($form->getHTML());
        }
    }

    protected function getForm() : \ilPropertyFormGUI
    {
        $form = new \ilPropertyFormGUI();
        $form->setTitle($this->txt("filesystem_configuration"));
        $form->setFormAction($this->ctrl->getFormAction($this));


        $msi = new \ilTextInputGUI($this->txt("path"), self::POST_PATH);
        $msi->setInfo($this->txt("path_info"));
        $msi->setRequired(true);
        $form->addItem($msi);

        $msi = new \ilTextInputGUI($this->txt("filetitle_template"), self::POST_FILETITLE_TEMPLATE);
        $msi->setRequired(true);
        $msi->setInfo($this->txt("filetitle_template_info"));
        $form->addItem($msi);

        $form->addCommandButton(self::CMD_SAVE, $this->txt("save"));
        return $form;
    }
}

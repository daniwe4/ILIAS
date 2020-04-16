<?php declare(strict_types = 1);

use CaT\Plugins\CancellationFeeReport\Settings\SettingsRepository as SettingsRepository;

class ilCancellationFeeReportSettingsGUI
{
    const CMD_VIEW = 'view_settings';
    const CMD_SAVE = 'save_settings';

    const POST_TITLE = 'title';
    const POST_DESCRIPTION = 'description';
    const POST_ONLINE = 'online';
    const POST_GLOBAL = 'global';

    protected $sr;
    protected $plugin;

    protected $object;

    protected $ctrl;
    protected $access;
    protected $tpl;


    public function __construct(
        SettingsRepository $sr,
        \ilCancellationFeeReportPlugin $plugin,
        \ilCtrl $ctrl,
        \ilAccess $access,
        \ilTemplate $tpl
    ) {
        $this->sr = $sr;
        $this->plugin = $plugin;
        $this->ctrl = $ctrl;
        $this->access = $access;
        $this->tpl = $tpl;
    }

    public function withObject(\ilObjCancellationFeeReport $object)
    {
        $this->object = $object;
        return $this;
    }

    public function executeCommand()
    {
        if (!$this->object) {
            throw new \LogicException('may not execute command without an object');
        }
        $this->cmd = $this->ctrl->getCmd(self::CMD_VIEW);
        if ($this->access->checkAccess("write", "", $this->object->getRefId())) {
            switch ($this->cmd) {
                case self::CMD_SAVE:
                    return $this->saveSettings();
                    break;
                case self::CMD_VIEW:
                    return $this->renderSettings();
                    break;
                default:
                    return $this->renderSettings();
            }
        }
    }

    protected function renderSettings()
    {
        $settings = $this->object->getSettings();
        $form = $this->buildForm();
        $form->getItemByPostVar(self::POST_TITLE)->setValue($this->object->getTitle());
        $form->getItemByPostVar(self::POST_DESCRIPTION)->setValue($this->object->getDescription());
        if ($settings->online()) {
            $form->getItemByPostVar(self::POST_ONLINE)->setChecked(true);
        }
        if ($settings->isGlobal()) {
            $form->getItemByPostVar(self::POST_GLOBAL)->setChecked(true);
        }
        $this->tpl->setContent($form->getHtml());
    }

    protected function saveSettings()
    {
        $form = $this->buildForm();
        $form->setValuesByPost();
        if ($form->checkInput()) {
            $this->saveSettingsData($form);
            $red = $this->ctrl->getLinkTarget($this, self::CMD_VIEW, '', false, false);
            ilUtil::sendSuccess($this->plugin->txt('settings_saved_confirm'), true);
            ilUtil::redirect($red);
        }
        $this->tpl->setContent($form->getHtml());
    }

    public function saveSettingsData($form)
    {
        $this->object->setTitle($form->getItemByPostVar(self::POST_TITLE)->getValue());
        $this->object->setDescription($form->getItemByPostVar(self::POST_DESCRIPTION)->getValue());
        $this->object->setSettings(
            $this->object->getSettings()
                ->withOnline((bool) $form->getItemByPostVar(self::POST_ONLINE)->getChecked())
                ->withGlobal((bool) $form->getItemByPostVar(self::POST_GLOBAL)->getChecked())
        );

        $this->object->update();
    }

    protected function buildForm()
    {
        $settings_form = new ilPropertyFormGUI();
        $settings_form->setFormAction($this->ctrl->getFormAction($this));
        $settings_form->addCommandButton(self::CMD_SAVE, $this->plugin->txt("save"));

        $title = new ilTextInputGUI($this->plugin->txt('title'), self::POST_TITLE);
        $title->setRequired(true);
        $settings_form->addItem($title);

        $description = new ilTextAreaInputGUI($this->plugin->txt('description'), self::POST_DESCRIPTION);
        $settings_form->addItem($description);

        $online = new ilCheckboxInputGUI($this->plugin->txt('online'), self::POST_ONLINE);
        $online->setValue(1);
        $settings_form->addItem($online);

        $global = new ilCheckboxInputGUI($this->plugin->txt('global'), self::POST_GLOBAL);
        $global->setValue(1);
        $settings_form->addItem($global);
        return $settings_form;
    }
}

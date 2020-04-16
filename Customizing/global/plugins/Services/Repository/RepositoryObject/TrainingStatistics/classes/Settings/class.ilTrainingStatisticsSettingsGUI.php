<?php

require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';

use CaT\Plugins\TrainingStatistics as TS;

class ilTrainingStatisticsSettingsGUI
{
    const CMD_VIEW = 'view_settings';
    const CMD_SAVE = 'save_settings';

    const POST_TITLE = 'title';
    const POST_DESCRIPTION = 'description';
    const POST_AGGREGATE = 'aggregate';
    const POST_ONLINE = 'online';
    const POST_GLOBAL = 'global';

    protected $object;
    protected $plugin;

    protected $g_ctrl;
    protected $g_access;
    protected $g_tpl;

    public function __construct($a_parent_gui, $plugin, $object)
    {
        $this->object = $object;
        $this->plugin = $plugin;

        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->g_access = $DIC->access();
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->g_lng = $DIC->language();
    }

    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd(self::CMD_VIEW);
        $this->cmd = $cmd;
        switch ($cmd) {
            case self::CMD_SAVE:
                if ($this->g_access->checkAccess("write", "", $this->object->getRefId())) {
                    return $this->saveSettings();
                }
                break;
            case self::CMD_VIEW:
                if ($this->g_access->checkAccess("write", "", $this->object->getRefId())) {
                    return $this->renderSettings();
                }
                break;
            default:
                if ($this->g_access->checkAccess("write", "", $this->object->getRefId())) {
                    return $this->renderSettings();
                }
        }
    }

    protected function renderSettings()
    {
        $settings = $this->object->settings();
        $form = $this->buildForm();
        $form->getItemByPostVar(self::POST_TITLE)->setValue($this->object->getTitle());
        $form->getItemByPostVar(self::POST_DESCRIPTION)->setValue($this->object->getDescription());
        $form->getItemByPostVar(self::POST_AGGREGATE)->setValue($settings->aggregateId());

        if ($settings->online()) {
            $form->getItemByPostVar(self::POST_ONLINE)->setChecked(true);
        }
        if ($settings->global()) {
            $form->getItemByPostVar(self::POST_GLOBAL)->setChecked(true);
        }
        $this->g_tpl->setContent($form->getHtml());
    }

    protected function saveSettings()
    {
        $form = $this->buildForm();
        $form->setValuesByPost();
        if ($form->checkInput()) {
            $this->saveSettingsData($form);
            $red = $this->g_ctrl->getLinkTarget($this, self::CMD_VIEW, '', false, false);
            ilUtil::sendSuccess($this->plugin->txt('settings_saved_confirm'), true);
            ilUtil::redirect($red);
        }
        $this->g_tpl->setContent($form->getHtml());
    }

    public function saveSettingsData($form)
    {
        $this->object->setTitle($form->getItemByPostVar(self::POST_TITLE)->getValue());
        $this->object->setDescription($form->getItemByPostVar(self::POST_DESCRIPTION)->getValue());
        $this->object->setSettings(
            $this->object->settings()
                ->withOnline((bool) $form->getItemByPostVar(self::POST_ONLINE)->getChecked())
                ->withGlobal((bool) $form->getItemByPostVar(self::POST_GLOBAL)->getChecked())
            ->withAggregateId((string) $form->getItemByPostVar(self::POST_AGGREGATE)->getValue())
        );
        $this->object->update();
    }

    protected function buildForm()
    {
        $settings_form = new ilPropertyFormGUI();
        $settings_form->setFormAction($this->g_ctrl->getFormAction($this));
        $settings_form->addCommandButton(self::CMD_SAVE, $this->g_lng->txt("save"));

        $title = new ilTextInputGUI($this->g_lng->txt('title'), self::POST_TITLE);
        $title->setRequired(true);
        $settings_form->addItem($title);

        $description = new ilTextAreaInputGUI($this->g_lng->txt('description'), self::POST_DESCRIPTION);
        $settings_form->addItem($description);

        $aggregate = new ilSelectInputGUI($this->plugin->txt('aggregate'), self::POST_AGGREGATE);
        $aggregate_options = [];
        foreach (TS\Report::$aggregate_options as $option) {
            $aggregate_options[$option] = $this->plugin->txt($option);
        }
        asort($aggregate_options);
        $aggregate->setOptions(array_merge([TS\Settings\Settings::AGGREGATE_ID_NONE => $this->g_lng->txt(TS\Settings\Settings::AGGREGATE_ID_NONE)], $aggregate_options));
        $settings_form->addItem($aggregate);

        $online = new ilCheckboxInputGUI($this->g_lng->txt('online'), self::POST_ONLINE);
        $online->setValue(1);
        $settings_form->addItem($online);

        $global = new ilCheckboxInputGUI($this->g_lng->txt('global'), self::POST_GLOBAL);
        $global->setValue(1);
        $settings_form->addItem($global);

        return $settings_form;
    }
}

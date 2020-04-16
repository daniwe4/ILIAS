<?php

declare(strict_types=1);

use \CaT\Plugins\TrainingStatisticsByOrgUnits\Settings\Settings;

class ilTSBOUSettingsGUI
{
    const CMD_SHOW_SETTINGS = "showSettings";
    const CMD_SAVE_SETTINGS = "saveSettings";

    const F_TITLE = "title";
    const F_DESCRIPTION = "description";
    const F_IS_ONLINE = "is_online";
    const F_IS_GLOBAL = "is_global";

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var Closure
     */
    protected $txt;

    /**
     * @var ilObjTrainingStatisticsByOrgUnits
     */
    protected $object;

    public function __construct(
        ilCtrl $ctrl,
        ilTemplate $tpl,
        Closure $txt,
        ilObjTrainingStatisticsByOrgUnits $object
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->txt = $txt;
        $this->object = $object;
    }

    /**
     * @throws Exception if cmd is unknown
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_SHOW_SETTINGS:
                $this->showSettings();
                break;
            case self::CMD_SAVE_SETTINGS:
                $this->saveSettings();
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    protected function showSettings(ilPropertyFormGUI $form = null)
    {
        if (is_null($form)) {
            $form = $this->initForm();
            $this->fillForm($form);
        }

        $this->tpl->setContent($form->getHTML());
    }

    protected function saveSettings()
    {
        $form = $this->initForm();
        $form->setValuesByPost();
        if (!$form->checkInput()) {
            $this->showSettings($form);
            return;
        }

        $title = $form->getItemByPostVar(self::F_TITLE)->getValue();
        $this->object->setTitle($title);

        $description = $form->getItemByPostVar(self::F_DESCRIPTION)->getValue();
        $this->object->setDescription($description);

        $is_online = (bool) $form->getItemByPostVar(self::F_IS_ONLINE)->getChecked();
        $is_global = (bool) $form->getItemByPostVar(self::F_IS_GLOBAL)->getChecked();
        $fnc = function (Settings $s) use ($is_online, $is_global) {
            return $s->withIsOnline($is_online)->withIsGlobal($is_global);
        };



        $this->object->updateSettings($fnc);
        $this->object->update();

        ilUtil::sendSuccess($this->txt("settings_saved"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW_SETTINGS);
    }

    protected function initForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->txt("settings"));
        
        $ti = new ilTextInputGUI($this->txt("title"), self::F_TITLE);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ta = new ilTextAreaInputGUI($this->txt("description"), self::F_DESCRIPTION);
        $form->addItem($ta);

        $cb = new ilCheckboxInputGUI($this->txt("is_online"), self::F_IS_ONLINE);
        $cb->setValue(1);
        $form->addItem($cb);

        $cb = new ilCheckboxInputGUI($this->txt("is_global"), self::F_IS_GLOBAL);
        $cb->setValue(1);
        $form->addItem($cb);

        $form->addCommandButton(self::CMD_SAVE_SETTINGS, $this->txt("save"));
        $form->addCommandButton(self::CMD_SHOW_SETTINGS, $this->txt("cancel"));
        return $form;
    }

    protected function fillForm(ilPropertyFormGUI $form)
    {
        $settings = $this->object->getSettings();
        $values = [
            self::F_TITLE => $this->object->getTitle(),
            self::F_DESCRIPTION => $this->object->getDescription(),
            self::F_IS_ONLINE => (int) $settings->isOnline(),
            self::F_IS_GLOBAL => (int) $settings->isGlobal()
        ];
        $form->setValuesByArray($values);
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}

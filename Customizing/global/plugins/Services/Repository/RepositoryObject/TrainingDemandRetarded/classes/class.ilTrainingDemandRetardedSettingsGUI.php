<?php

require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';

class ilTrainingDemandRetardedSettingsGUI
{
    const CMD_VIEW = 'view_settings';
    const CMD_SAVE = 'save_settings';

    const POST_TITLE = 'title';
    const POST_DESCRIPTION = 'description';
    const POST_ONLINE = 'online';
    const POST_GLOBAL = 'global';
    const POST_LOCAL_ROLES = 'local_roles';
    const POST_ROLES = "roles";

    /**
     * @var ilObjTrainingDemandRetarded
     */
    protected $object;

    /**
     * @var Closure
     */
    protected $txt;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilAccess
     */
    protected $access;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    public function __construct(
        ilCtrl $ctrl,
        ilAccess $access,
        ilGlobalTemplateInterface $tpl,
        ilLanguage $lng,
        ilObjTrainingDemandRetarded $object,
        Closure $txt
    ) {
        $this->ctrl = $ctrl;
        $this->access = $access;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->object = $object;
        $this->txt = $txt;
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_SAVE:
                if ($this->access->checkAccess("write", "", $this->object->getRefId())) {
                    $this->saveSettings();
                }
                break;
            case self::CMD_VIEW:
                if ($this->access->checkAccess("write", "", $this->object->getRefId())) {
                    $this->renderSettings();
                }
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    protected function renderSettings(ilPropertyFormGUI $form = null)
    {
        if (is_null($form)) {
            $form = $this->buildForm();
            $this->fillForm($form);
        }

        $this->tpl->setContent($form->getHtml());
    }

    protected function saveSettings()
    {
        $form = $this->buildForm();
        $form->setValuesByPost();
        if (!$form->checkInput()) {
            $this->renderSettings($form);
            return;
        }

        $this->saveSettingsData($form);
        ilUtil::sendSuccess($this->txt('settings_saved_confirm'), true);
        $this->ctrl->redirect($this, self::CMD_VIEW);
    }

    public function saveSettingsData($form)
    {
        $this->object->setTitle($form->getItemByPostVar(self::POST_TITLE)->getValue());
        $this->object->setDescription($form->getItemByPostVar(self::POST_DESCRIPTION)->getValue());
        $this->object->setSettings(
            $this->object->settings()
                ->withOnline((bool) $form->getItemByPostVar(self::POST_ONLINE)->getChecked())
                ->withGlobal((bool) $form->getItemByPostVar(self::POST_GLOBAL)->getChecked())
                ->withLocalRoles((array) $form->getInput(self::POST_ROLES))
        );
        $this->object->update();
    }

    protected function buildForm()
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->addCommandButton(self::CMD_SAVE, $this->txt("save"));

        $title = new ilTextInputGUI($this->txt('title'), self::POST_TITLE);
        $title->setRequired(true);
        $form->addItem($title);

        $description = new ilTextAreaInputGUI($this->txt('description'), self::POST_DESCRIPTION);
        $form->addItem($description);

        $local_roles = new ilCheckboxInputGUI($this->txt("local_roles"), self::POST_LOCAL_ROLES);

        $options = [
            "il_crs_member" => $this->globalTxt("il_crs_member"),
            "il_crs_admin" => $this->globalTxt("il_crs_admin"),
            "il_crs_tutor" => $this->globalTxt("il_crs_tutor")
        ];
        $roles = new ilMultiSelectInputGUI("", self::POST_ROLES);
        $roles->setOptions($options);
        $local_roles->addSubItem($roles);
        $form->addItem($local_roles);

        $online = new ilCheckboxInputGUI($this->txt('online'), self::POST_ONLINE);
        $online->setValue(1);
        $form->addItem($online);

        $global = new ilCheckboxInputGUI($this->txt('global'), self::POST_GLOBAL);
        $global->setValue(1);
        $form->addItem($global);

        return $form;
    }

    protected function fillForm(ilPropertyFormGUI $form)
    {
        $settings = $this->object->settings();
        $local_roles = $settings->getLocalRoles();
        $values = [
            self::POST_TITLE => $this->object->getTitle(),
            self::POST_DESCRIPTION => $this->object->getDescription(),
            self::POST_ONLINE => $settings->online(),
            self::POST_GLOBAL => $settings->isGlobal(),
            self::POST_ROLES => $local_roles,
            self::POST_LOCAL_ROLES => count($local_roles) > 0
        ];
        $form->setValuesByArray($values);
    }
    
    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }

    protected function globalTxt(string $code) : string
    {
        return $this->lng->txt($code);
    }
}

<?php

declare(strict_types=1);

use CaT\Plugins\TrainerOperations\AccessHelper;
use CaT\Plugins\TrainerOperations\ObjTrainerOperations;
use CaT\Plugins\TrainerOperations\Aggregations;

/**
 * GUI for general settings of the object.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class ilTrainerOperationsSettingsGUI
{
    const CMD_EDIT_PROPERTIES = "editProperties";
    const CMD_SAVE_PROPERTIES = "saveProperties";
    const CMD_CANCEL = "cancel";

    const F_SETTINGS_TITLE = "settings_title";
    const F_SETTINGS_DESCRIPTION = "settings_description";
    const F_SETTINGS_ROLES = "settings_roles";

    /**
     * @var AccessHelper
     */
    protected $access;

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var \ilPropertyFormGUI|null
     */
    protected $form;

    /**
     * @var \ilPropertyFormGUI
     */
    protected $base_form;

    /**
     * @var \ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var \ilTemplate
     */
    protected $g_tpl;

    /**
     * @var ObjTrainerOperations
     */
    protected $object;

    /**
     * @var Aggregations\Roles
     */
    protected $role_utils;

    public function __construct(
        AccessHelper $access,
        \Closure $txt,
        \ilPropertyFormGUI $form,
        \ilCtrl $g_ctrl,
        \ilTemplate $g_tpl,
        ObjTrainerOperations $object,
        Aggregations\Roles $role_utils
    ) {
        $this->access = $access;
        $this->txt = $txt;
        $this->base_form = $form;
        $this->form = null;
        $this->g_ctrl = $g_ctrl;
        $this->g_tpl = $g_tpl;
        $this->object = $object;
        $this->role_utils = $role_utils;
    }

    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd();

        if (!$this->access->mayEditSettings()) {
            $this->access->redirectInfo('disallowed_settings');
        }

        switch ($cmd) {
            case self::CMD_EDIT_PROPERTIES:
                $this->editProperties();
                break;
            case self::CMD_SAVE_PROPERTIES:
                $this->saveProperties();
                break;
            case self::CMD_CANCEL:
                $this->cancel();
                break;
            default:
                throw new Exception(__METHOD__ . " :: Unknown command " . $cmd);
        }
    }

    protected function editProperties()
    {
        if ($this->form === null) {
            $this->initForm();
        }
        $this->fillForm();
        $this->g_tpl->setContent($this->form->getHtml());
    }

    protected function saveProperties()
    {
        $this->initForm();

        if (!$this->form->checkInput()) {
            $this->form->setValuesByPost();
            $this->editProperties();
            return;
        }

        $post = $_POST;
        $this->object->setTitle($post[self::F_SETTINGS_TITLE]);
        $this->object->setDescription($post[self::F_SETTINGS_DESCRIPTION]);

        $roles = array_map('intval', $post[self::F_SETTINGS_ROLES]);
        $fnc = function ($s) use ($roles) {
            return $s->withGlobalRoles($roles);
        };
        $this->object->updateSettings($fnc);

        $this->object->update();

        \ilUtil::sendSuccess($this->txt("save_settings_success"), true);
        $this->g_ctrl->redirect($this, self::CMD_EDIT_PROPERTIES);
    }

    protected function cancel()
    {
        $this->g_ctrl->redirect($this, self::CMD_EDIT_PROPERTIES);
    }


    protected function initForm()
    {
        $this->form = $this->base_form;
        $this->form->setFormAction($this->g_ctrl->getFormAction($this));
        $this->form->setTitle($this->txt("settings_form_title"));
        $this->form->setFormAction($this->g_ctrl->getFormAction($this));

        $this->form->addCommandButton(self::CMD_SAVE_PROPERTIES, $this->txt("save"));
        $this->form->addCommandButton(self::CMD_CANCEL, $this->txt("cancel"));

        $ti = new \ilTextInputGUI($this->txt("settings_title"), self::F_SETTINGS_TITLE);
        $ti->setRequired(true);
        $this->form->addItem($ti);

        $ta = new \ilTextAreaInputGUI($this->txt("settings_description"), self::F_SETTINGS_DESCRIPTION);
        $this->form->addItem($ta);

        $options = $this->role_utils->getGlobalRoles();

        $ms = new \ilMultiSelectInputGUI($this->txt("settings_roles"), self::F_SETTINGS_ROLES);
        $ms->setOptions($options);
        $ms->setInfo($this->txt('settings_roles_byline'));
        $ms->setHeight(200);
        $ms->setWidth(250);
        $this->form->addItem($ms);
    }

    protected function fillForm()
    {
        $settings = $this->object->getSettings();
        $values = [
            self::F_SETTINGS_TITLE => $this->object->getTitle(),
            self::F_SETTINGS_DESCRIPTION => $this->object->getDescription(),
            self::F_SETTINGS_ROLES => $settings->getGlobalRoles()
        ];
        $this->form->setValuesByArray($values);
    }

    public function txt(string $code) : string
    {
        assert('is_string($code)');
        $txt = $this->txt;
        return $txt($code);
    }
}

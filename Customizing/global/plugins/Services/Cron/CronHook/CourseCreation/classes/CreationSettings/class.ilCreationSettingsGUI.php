<?php

declare(strict_types=1);

use CaT\Plugins\CourseCreation\ilActions;

class ilCreationSettingsGUI
{
    const CMD_SHOW_SETTINGS = "showSettings";
    const CMD_SAVE_SETTINGS = "saveSettings";

    const ROLES = "roles";

    /**
     * @var \ilCtrl
     */
    protected $ctrl;

    /**
     * @var \ilTemplate
     */
    protected $tpl;

    /**
     * @var ilActions
     */
    protected $actions;

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var \CaT\Plugins\MaterialList\ilPluginActions | null
     */
    protected $plugin_actions;

    public function __construct(
        \ilCtrl $ctrl,
        \ilTemplate $tpl,
        \ilRbacReview $rbacreview,
        ilActions $actions,
        \Closure $txt
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->rbacreview = $rbacreview;
        $this->actions = $actions;
        $this->txt = $txt;
    }

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
                throw new \Exception("Unknown command: " . $cmd);
        }
    }

    protected function showSettings(\ilPropertyFormGUI $form = null)
    {
        if (is_null($form)) {
            $form = $this->initForm();
            $this->fillForm($form);
        }

        $this->tpl->setContent($form->getHtml());
    }

    protected function saveSettings()
    {
        $form = $this->initForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showSettings($form);
            return;
        }

        $post = $_POST;
        $roles = array_map(
            function ($role_id) {
                return (int) $role_id;
            },
            $post[self::ROLES]
        );
        $this->actions->saveRoleIdsForMultiplyRequestCreation($roles);
        ilUtil::sendSuccess($this->txt("roles_sucessfully_saved"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW_SETTINGS);
    }

    protected function initForm() : \ilPropertyFormGUI
    {
        $form = new \ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->txt("creation_settings"));
        $form->addCommandButton(self::CMD_SAVE_SETTINGS, $this->txt("save"));
        $form->addCommandButton(self::CMD_SHOW_SETTINGS, $this->txt("cancel"));

        $sel = new \ilMultiSelectInputGUI($this->txt("select_roles"), self::ROLES);
        $sel->setWidthUnit("%");
        $sel->setWidth(100);
        $sel->setHeight(200);
        $roles = [];
        foreach ($this->rbacreview->getGlobalRoles() as $role_id) {
            $roles[$role_id] = \ilObject::_lookupTitle($role_id);
        }
        $sel->setOptions($roles);

        $form->addItem($sel);

        return $form;
    }

    protected function fillForm(\ilPropertyFormGUI $form)
    {
        $values = [];
        $values[self::ROLES] = $this->actions->getRoleIdsForMultiplyRequestCreation();

        $form->setValuesByArray($values);
    }

    protected function txt(string $code) : string
    {
        $txt = $this->txt;
        return $txt($code);
    }
}

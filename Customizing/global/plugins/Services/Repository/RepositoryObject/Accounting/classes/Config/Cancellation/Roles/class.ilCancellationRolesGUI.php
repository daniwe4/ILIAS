<?php

declare(strict_types=1);

use CaT\Plugins\Accounting\Config\Cancellation\Roles;

class ilCancellationRolesGUI
{
    const CMD_SHOW_ROLES = "showRoles";
    const CMD_SAVE_ROLES = "saveRoles";

    const F_ROLES = "roles";

    protected $ctrl;
    protected $tpl;
    protected $rbacreview;
    protected $txt;
    protected $roles_db;

    public function __construct(
        ilCtrl $ctrl,
        ilTemplate $tpl,
        ilRbacReview $rbacreview,
        Closure $txt,
        Roles\DB $roles_db
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->rbacreview = $rbacreview;
        $this->txt = $txt;
        $this->roles_db = $roles_db;
    }

    /**
     * @inheritDoc
     * @throws Exception if cmd is not known
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_SHOW_ROLES:
                $this->showRoles();
                break;
            case self::CMD_SAVE_ROLES:
                $this->saveRoles();
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    protected function showRoles(ilPropertyFormGUI $form = null)
    {
        if (is_null($form)) {
            $form = $this->initForm();
            $this->fillForm($form);
        }

        $this->tpl->setContent($form->getHTML());
    }

    protected function saveRoles()
    {
        $form = $this->initForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showRoles($form);
            return;
        }

        $this->roles_db->saveRoles($_POST[self::F_ROLES]);

        ilUtil::sendSuccess($this->txt("cancellation_roles_saved"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW_ROLES);
    }

    protected function initForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->txt("cancellation_roles"));
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->addCommandButton(self::CMD_SAVE_ROLES, $this->txt("save"));
        $form->addCommandButton(self::CMD_SHOW_ROLES, $this->txt("cancel"));

        $mi = new ilMultiSelectInputGUI($this->txt("roles"), self::F_ROLES);
        $mi->setWidthUnit("%");
        $mi->setWidth(100);
        $mi->setHeight(200);
        $mi->setOptions($this->getGlobalRoleOptions());
        $form->addItem($mi);

        return $form;
    }

    /**
     * @return string[]
     */
    protected function getGlobalRoleOptions() : array
    {
        $ret = [];
        foreach ($this->rbacreview->getGlobalRoles() as $role_id) {
            $ret[$role_id] = ilObject::_lookupTitle($role_id);
        }

        return $ret;
    }

    protected function fillForm(ilPropertyFormGUI $form)
    {
        $values = [
            self::F_ROLES => $this->roles_db->getRoles()
        ];

        $form->setValuesByArray($values);
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}

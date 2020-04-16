<?php

use CaT\Plugins\BookingModalities\ilActions;

/**
 * Config GUI which roles might be selected in the repo object
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilSelectableRolesGUI
{
    const CMD_SHOW_ROLES = "showRoles";
    const CMD_SAVE_ROLES = "saveRoles";

    const F_ROLES = "roles";

    /**
     * @var ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var ilTemplate
     */
    protected $g_tpl;

    /**
     * @var \ilBookingModalitiesConfigGUI
     */
    protected $parent;

    /**
     * @var ilActions
     */
    protected $actions;

    public function __construct(\ilBookingModalitiesConfigGUI $parent, ilActions $actions)
    {
        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->parent = $parent;
        $this->actions = $actions;
    }

    /**
     * @inheritdoc
     */
    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd(self::CMD_SHOW_ROLES);
        switch ($cmd) {
            case self::CMD_SHOW_ROLES:
                $this->showRoles();
                break;
            case self::CMD_SAVE_ROLES:
                $this->saveRoles();
                break;
            default:
                throw new Exception("Unknown command " . $cmd);
        }
    }

    /**
     * Show form to select all orgunit local roles
     * Selected roles are checked
     *
     * @return null
     */
    public function showRoles()
    {
        $form = $this->initForm();
        $this->fillForm($form);

        $this->g_tpl->setContent($form->getHtml());
    }

    /**
     * Save selected roles
     *
     * @return null
     */
    protected function saveRoles()
    {
        $roles = $_POST[self::F_ROLES];

        if ($roles === null) {
            $roles = array();
        }
        $this->actions->saveRoles($roles);

        ilUtil::sendSuccess($this->txt("roles_successful_saved"), true);
        $this->g_ctrl->redirect($this, self::CMD_SHOW_ROLES);
    }

    /**
     * Init the role form
     *
     * @return \ilPropertyFormGUI
     */
    protected function initForm()
    {
        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new \ilPropertyFormGUI();
        $form->setTitle($this->txt("select_roles"));
        $form->setFormAction($this->g_ctrl->getFormAction($this));

        require_once("Services/Form/classes/class.ilMultiSelectInputGUI.php");
        $msi = new \ilMultiSelectInputGUI($this->txt("roles"), self::F_ROLES);
        $msi->setWidthUnit('%');
        $msi->setWidth(100);
        $msi->setOptions($this->translateTitle($this->actions->getRoleOptions()));
        $msi->setInfo($this->txt("roles_info"));
        $form->addItem($msi);

        $form->addCommandButton(self::CMD_SAVE_ROLES, $this->txt("save"));

        return $form;
    }

    /**
     * Fills the form with values
     *
     * @return null
     */
    protected function fillForm(\ilPropertyFormGUI $form)
    {
        $assigned_roles = $this->actions->getAssignedRoles();
        $values = array();
        $values[self::F_ROLES] = $assigned_roles;

        $form->setValuesByArray($values);
    }

    /**
     * Translates il_role titles
     *
     * @param string[] 	$title
     *
     * @return string[]
     */
    protected function translateTitle($orgu_options)
    {
        foreach ($orgu_options as $key => $orgu_option) {
            $length = strlen("il_");
            if (substr($orgu_option, 0, $length) === "il_") {
                $orgu_options[$key] = $this->txt($orgu_option);
            }
        }

        return $orgu_options;
    }

    /**
     * Translate var to text
     *
     * @param string 	$code
     *
     * @return $code
     */
    protected function txt($code)
    {
        return $this->actions->getPlugin()->txt($code);
    }
}

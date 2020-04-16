<?php

use CaT\Plugins\EduTracking\Purposes\GTI\Configuration;

/**
 * GUI to configurate the GTI basic settings
 *
 * @ilCtrl_Calls ilConfigGTIGUI: ilGTICategoriesGUI
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilConfigGTIGUI
{
    const CMD_SHOW = "show";
    const CMD_SAVE = "save";
    const CMD_CANCEL = "cancel";

    const F_AVAILABLE = "f_available";

    /**
     * @var ilEduTrackingConfigGUI
     */
    protected $parent;

    /**
     * @var ilActions
     */
    protected $actions;

    public function __construct(ilEduTrackingConfigGUI $parent, Configuration\ilActions $actions)
    {
        global $DIC;

        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->g_user = $DIC->user();
        $this->g_tabs = $DIC->tabs();

        $this->parent = $parent;
        $this->actions = $actions;

        require_once $this->actions->getPlugin()->getDirectory() . "/classes/Purposes/GTI/Configuration/class.ilGTICategoriesGUI.php";
    }

    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd();
        $next_class = $this->g_ctrl->getNextClass();
        $this->setSubTabs($cmd);

        switch ($next_class) {
            case 'ilgticategoriesgui':
                $this->forwardCategories();
                break;
            default:
                switch ($cmd) {
                    case self::CMD_SHOW:
                    case self::CMD_CANCEL:
                        $this->show();
                        break;
                    case self::CMD_SAVE:
                        $this->save();
                        break;
                    default:
                        throw new Exception("Unknowm command: " . $cmd);
                }
        }
    }

    /**
     * Show all configuration options
     *
     * @return void
     */
    protected function show()
    {
        $form = $this->initForm();
        $this->fillForm($form);
        $this->g_tpl->setContent($form->getHtml());
    }

    protected function forwardCategories()
    {
        $gui = new ilGTICategoriesGUI($this->parent, $this->actions);
        $this->g_ctrl->forwardCommand($gui);
    }

    protected function setSubTabs(string $active_tab)
    {
        $this->g_tabs->addSubTab(
            self::CMD_SHOW,
            $this->txt("availability"),
            $this->g_ctrl->getLinkTarget($this, self::CMD_SHOW)
        );

        $this->g_tabs->addSubTab(
            ilGTICategoriesGUI::CMD_SHOW_CATEGORIES,
            $this->txt("categories"),
            $this->g_ctrl->getLinkTargetByClass(array("ilGTICategoriesGUI"), ilGTICategoriesGUI::CMD_SHOW_CATEGORIES)
        );

        $this->g_tabs->setSubTabActive($active_tab);
    }
    /**
     * Saves all user made configutation changes
     *
     * @return void
     */
    protected function save()
    {
        $post = $_POST;

        $available = isset($post[self::F_AVAILABLE]) && (int) $post[self::F_AVAILABLE] === 1;

        $this->actions->create($available, $this->g_user->getId());

        ilUtil::sendSuccess($this->txt("configuration_saved"), true);
        $this->g_ctrl->redirect($this, self::CMD_SHOW);
    }

    /**
     * Creates the form for configuration values
     *
     * @return ilPropertyFormGUI
     */
    protected function initForm()
    {
        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->g_ctrl->getFormAction($this));
        $form->addCommandButton(self::CMD_SAVE, $this->txt("save"));
        $form->addCommandButton(self::CMD_CANCEL, $this->txt("cancel"));
        $form->setTitle($this->txt("configuration"));

        $cb = new ilCheckboxInputGUI($this->txt("available"), self::F_AVAILABLE);
        $cb->setValue(1);
        $form->addItem($cb);

        return $form;
    }

    /**
     * Fills the form with current values
     *
     * @param ilPropertyFormGUI 	$form
     *
     * @return void
     */
    protected function fillForm(ilPropertyFormGUI $form)
    {
        $values = array();
        $current = $this->actions->select();
        if ($current !== null) {
            $values[self::F_AVAILABLE] = $current->getAvailable();
        }

        $form->setValuesByArray($values);
    }

    /**
     * @param 	string	$code
     * @return	string
     */
    protected function txt($code)
    {
        return $this->actions->getPlugin()->txt($code);
    }
}

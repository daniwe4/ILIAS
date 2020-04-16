<?php

use CaT\Plugins\EduTracking\Purposes\WBD\Configuration;
use CaT\Plugins\EduTracking\Purposes\WBD\Configuration\ConfigWBD as CWBD;

/**
 * GUI to configurate the WBD basic settings
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilConfigWBDGUI
{
    const CMD_SHOW = "show";
    const CMD_SAVE = "save";
    const CMD_CANCEL = "cancel";
    const CMD_AUTOCOMPLETE = "autoComplete";

    const F_AVAILABLE = "f_available";
    const F_CONTACT = "f_contact";
    const F_USER = "f_user";



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

        $this->parent = $parent;
        $this->actions = $actions;
    }

    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd();

        switch ($cmd) {
            case self::CMD_SHOW:
            case self::CMD_CANCEL:
                $this->show();
                break;
            case self::CMD_SAVE:
                $this->save();
                break;
            case self::CMD_AUTOCOMPLETE:
                $this->autoComplete();
                break;
            default:
                throw new Exception("Unknowm command: " . $cmd);
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

    /**
     * Saves all user made configutation changes
     *
     * @return void
     */
    protected function save()
    {
        $post = $_POST;

        $available = isset($post[self::F_AVAILABLE]) && (int) $post[self::F_AVAILABLE] === 1;
        $contact = $post[self::F_CONTACT];
        $user = null;
        if ($contact == CWBD::M_FIX_CONTACT) {
            require_once("Services/User/classes/class.ilObjUser.php");
            $user = ilObjUser::_lookupId($post[self::F_USER]);
        }

        $this->actions->create($available, $contact, $user, $this->g_user->getId());

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

        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->txt("data_source"));
        $form->addItem($sh);

        $rg = new ilRadioGroupInputGUI($this->txt("contact"), self::F_CONTACT);
        $option = new ilRadioOption($this->txt(CWBD::M_FIX_CONTACT), CWBD::M_FIX_CONTACT);
        $ti = new ilTextInputGUI($this->txt("user"), self::F_USER);
        $autocomplete_link = $this->g_ctrl->getLinkTarget($this, self::CMD_AUTOCOMPLETE, "", true);
        $ti->setDataSource($autocomplete_link);
        $option->addSubItem($ti);

        $rg->addOption($option);
        $option = new ilRadioOption($this->txt(CWBD::M_COURSE_TUTOR), CWBD::M_COURSE_TUTOR);
        $rg->addOption($option);
        $option = new ilRadioOption($this->txt(CWBD::M_COURSE_ADMIN), CWBD::M_COURSE_ADMIN);
        $rg->addOption($option);
        $option = new ilRadioOption($this->txt(CWBD::M_XCCL_CONTACT), CWBD::M_XCCL_CONTACT);
        $rg->addOption($option);

        $form->addItem($rg);

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

        $values[self::F_CONTACT] = CWBD::M_FIX_CONTACT;
        if ($current !== null) {
            $values[self::F_AVAILABLE] = $current->getAvailable();
            $values[self::F_CONTACT] = $current->getContact();
            require_once("Services/User/classes/class.ilObjUser.php");
            $values[self::F_USER] = ilObjUser::_lookupLogin($current->getUserId());
        }

        $form->setValuesByArray($values);
    }

    /**
     * Provide informations for user autocomplete input gui
     *
     * @return void
     */
    protected function autoComplete()
    {
        include_once './Services/User/classes/class.ilUserAutoComplete.php';
        $auto = new ilUserAutoComplete();
        $auto->setSearchFields(array('login','firstname','lastname','email'));
        $auto->enableFieldSearchableCheck(false);
        if (($_REQUEST['fetchall'])) {
            $auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
        }
        echo $auto->getList($_REQUEST['term']);
        exit();
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

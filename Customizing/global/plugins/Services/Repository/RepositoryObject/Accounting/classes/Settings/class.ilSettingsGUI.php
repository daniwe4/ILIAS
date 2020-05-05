<?php

use \CaT\Plugins\Accounting\ilObjectActions;

require_once("Services/Utilities/classes/class.ilUtil.php");

/**
 * GUI for Settings
 *
 * @author Daniel Weise 	<daniel.weise@concepts-and-training.de>
 */
class ilSettingsGUI
{
    const CMD_EDIT_SETTINGS = "editSettings";
    const CMD_SAVE_SETTINGS = "saveSettings";
    const CMD_EDIT_PROPERTIES = "editProperties";

    const F_TITLE = "title";
    const F_DESCRIPTION = "description";
    const F_FINALIZED = "finalized";
    const F_EDIT_FEE = "edit_fee";

    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;
    /**
     * @var ilAccess
     */
    protected $access;
    /**
     * @var ilObjectActions
     */
    protected $actions;
    /**
     * @var Closure
     */
    protected $txt;

    public function __construct(
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
        ilAccess $access,
        ilObjectActions $actions,
        Closure $txt
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->access = $access;
        $this->actions = $actions;
        $this->txt = $txt;
    }

    /**
     * Delegate commands
     *
     * @throws Exception
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd(self::CMD_EDIT_SETTINGS);
        switch ($cmd) {
            case self::CMD_EDIT_PROPERTIES:
                $this->editSettings();
                break;
            case self::CMD_EDIT_SETTINGS:
            case self::CMD_SAVE_SETTINGS:
                $this->$cmd();
                break;
            default:
                throw new Exception(__METHOD__ . ": unkown command " . $cmd);
        }
    }

    /**
     * Create a editing GUI
     *
     * @param \ilPropertyformGUI 	$form
     */
    protected function editSettings($form = null)
    {
        if ($form === null) {
            $form = $this->initForm();
            $form = $this->fillForm($form);
        }

        $form->addCommandButton(self::CMD_SAVE_SETTINGS, $this->txt("xacc_save"));
        $form->addCommandButton(self::CMD_EDIT_SETTINGS, $this->txt("xacc_cancel"));

        $this->tpl->setContent($form->getHtml());
    }

    /**
     * Save settings to db
     */
    protected function saveSettings()
    {
        $form = $this->initForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->editSettings($form);
            return;
        }

        $post = $_POST;
        $title = $post[self::F_TITLE];
        $description = $post[self::F_DESCRIPTION];
        $finalized = (bool) $post[self::F_FINALIZED];
        $edit_fee = (bool) $post[self::F_EDIT_FEE];

        $object = $this->actions->getObject();
        $object->setTitle($title);
        $object->setDescription($description);

        $fnc = function ($s) use ($finalized, $edit_fee) {
            return $s
                ->withFinalized($finalized)
                ->withEditFee($edit_fee)
            ;
        };
        $object->updateSettings($fnc);
        $object->update();

        $this->actions->updatedEvent();
        ilUtil::sendSuccess($this->txt("settings_successful_saved"), true);
        $this->ctrl->redirect($this, self::CMD_EDIT_SETTINGS);
    }

    /**
     * Init a new settings form
     */
    protected function initForm()
    {
        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->txt("settings_header"));

        $ti = new ilTextInputGUI($this->txt("settings_title"), self::F_TITLE);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ta = new ilTextareaInputGUI($this->txt("settings_description"), self::F_DESCRIPTION);
        $form->addItem($ta);

        if ($this->access->checkAccess("cancel_finalize", "", $this->actions->getObject()->getRefId())) {
            $cb = new \ilCheckboxInputGUI($this->txt("settings_finalized"), self::F_FINALIZED);
            $form->addItem($cb);
        }

        $cb = new \ilCheckboxInputGUI($this->txt("settings_edit_fee"), self::F_EDIT_FEE);
        $cb->setInfo($this->txt("settings_edit_fee_info"));
        $cb->setValue(1);
        $form->addItem($cb);

        return $form;
    }

    /**
     * Fill the settings form
     *
     * @param \ilPropertyFormGUI 	$form
     * @return \ilPropertyFormGUI 	$form
     */
    protected function fillForm(\ilPropertyFormGUI $form)
    {
        $current = $this->actions->getObject();
        $settings = $current->getSettings();

        $values = array(
            self::F_TITLE => $current->getTitle(),
            self::F_DESCRIPTION => $current->getDescription(),
            self::F_FINALIZED => $settings->getFinalized(),
            self::F_EDIT_FEE => $settings->getEditFee()
        );

        $form->setValuesByArray($values);

        return $form;
    }

    public function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}

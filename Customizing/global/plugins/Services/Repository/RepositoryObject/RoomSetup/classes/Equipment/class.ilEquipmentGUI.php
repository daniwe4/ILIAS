<?php

use \CaT\Plugins\RoomSetup;

/**
 * Configuration GUI for equipment of room setup
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilEquipmentGUI
{
    const CMD_SHOW_CONTENT = "showContent";
    const CMD_CANCEL = "cancel";
    const CMD_SAVE = "saveEquipment";

    /**
     * @var ilRoomSetupSettingsGUI
     */
    protected $parent_object;

    /**
     * @var ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var ilTemplate
     */
    protected $g_tpl;

    /**
     * @var ilObjectActions
     */
    protected $object_actions;

    /**
     * @var ilPluginActions
     */
    protected $plugin_actions;

    /**
     * @var Closure
     */
    protected $txt;

    /**
     * @var bool
     */
    protected $edit;

    /**
     * @param bool 		$edit
     */
    public function __construct(
        \ilObjRoomSetupGUI $parent_object,
        RoomSetup\ilObjectActions $object_actions,
        RoomSetup\ilPluginActions $plugin_actions,
        Closure $txt,
        $edit
    ) {
        global $DIC;

        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->parent_object = $parent_object;
        $this->object_actions = $object_actions;
        $this->plugin_actions = $plugin_actions;
        $this->txt = $txt;
        $this->edit = $edit;
    }

    /**
     * Delegate current command to functions
     *
     * @throws Exception 	if command is not known
     *
     * @return null
     */
    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd(self::CMD_SHOW_CONTENT);

        switch ($cmd) {
            case self::CMD_SHOW_CONTENT:
            case self::CMD_CANCEL:
                $this->showContent();
                break;
            case self::CMD_SAVE:
                $this->saveEquipment();
                break;
            default:
                throw new Exception(__METHOD__ . " unknown command " . $cmd);
        }
    }

    /**
     * Show equipment for room setup
     *
     * @param ilPropertyFormGUI | null 	$form
     *
     * @return null
     */
    protected function showContent($form = null)
    {
        if ($form === null) {
            $form = $this->initForm();
            $this->fillForm($form);
        }

        $this->g_tpl->setContent($form->getHtml());
    }

    /**
     * Save the equipment
     *
     * @return null
     */
    protected function saveEquipment()
    {
        $form = $this->initForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showContent($form);
            return;
        }

        $post = $_POST;
        $equipment = $this->object_actions->getEquipmentWith(
            $post[RoomSetup\ilObjectActions::F_SERVICE_OPTIONS],
            $post[RoomSetup\ilObjectActions::F_SPECIAL_WISHES],
            $post[RoomSetup\ilObjectActions::F_ROOM_INFORMATION],
            $post[RoomSetup\ilObjectActions::F_SEAT_ORDER]
        );
        $this->object_actions->updateEquipment($equipment);

        ilUtil::sendSuccess($this->txt("equipment_save_success"), true);
        $this->g_ctrl->redirect($this);
    }

    /**
     * Get the form for equipment
     *
     * @return ilPropertyFormGUI
     */
    protected function initForm()
    {
        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        require_once("Services/Form/classes/class.ilFormSectionHeaderGUI.php");
        require_once("Services/Form/classes/class.ilMultiSelectInputGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->g_ctrl->getFormAction($this));

        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->txt("service"));
        $form->addItem($sh);

        $msi = new ilMultiSelectInputGUI($this->txt("tags"), RoomSetup\ilObjectActions::F_SERVICE_OPTIONS);
        $options = $this->getFormOptions();
        $msi->setOptions($options);
        $msi->setDisabled(!$this->edit);
        $msi->setWidthUnit("%");
        $msi->setWidth(100);
        $msi->setHeightUnit("px");
        $msi->setHeight(200);
        $form->addItem($msi);

        $t = new ilTextareaInputGUI($this->txt("special_wishes"), RoomSetup\ilObjectActions::F_SPECIAL_WISHES);
        $t->setDisabled(!$this->edit);
        $form->addItem($t);

        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->txt("material"));
        $form->addItem($sh);

        $ta = new ilTextareaInputGUI($this->txt("room_information"), RoomSetup\ilObjectActions::F_ROOM_INFORMATION);
        $ta->setDisabled(!$this->edit);
        $form->addItem($ta);

        $ta = new ilTextareaInputGUI($this->txt("seat_order"), RoomSetup\ilObjectActions::F_SEAT_ORDER);
        $ta->setDisabled(!$this->edit);
        $form->addItem($ta);

        if ($this->edit) {
            $form->addCommandButton(self::CMD_SAVE, $this->txt("save"));
            $form->addCommandButton(self::CMD_CANCEL, $this->txt("cancel"));
        }

        return $form;
    }

    /**
     * Get the form option for service options inclusive inactive selected
     *
     * @return array
     */
    protected function getFormOptions()
    {
        $active_options = $this->plugin_actions->getServiceOptionsForFormItem();
        $keys_active_options = array_keys($active_options);
        $equipment = $this->object_actions->getEquipment();
        $missing = array_diff($equipment->getServiceOptions(), $keys_active_options);
        $missing_options = $this->plugin_actions->getMissingAssignedInactiveOptions($missing);
        $options = $missing_options + $active_options;

        asort($options);

        return $options;
    }

    /**
     * Filles form with current values
     *
     * @param ilPropertyFormGUI 	$form
     *
     * @return null
     */
    protected function fillForm($form)
    {
        $values = $this->object_actions->getFormValues();
        $form->setValuesByArray($values);
    }

    /**
     * Translate code to lang value
     *
     * @param string 	$code
     *
     * @return string
     */
    protected function txt($code)
    {
        assert('is_string($code)');
        $txt = $this->txt;
        return $txt($code);
    }
}

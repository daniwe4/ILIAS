<?php

declare(strict_types=1);

use \CaT\Plugins\RoomSetup;

/**
 * Plugin configuration base gui for service options.
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilServiceOptionsGUI
{
    const CMD_SHOW_SERVICE_OPTIONS = "showServiceOptions";
    const CMD_SHOW_CONFIRMATION_EDIT = "showConfirmationEdit";
    const CMD_SHOW_CONFIRMATION_MULTI_DELETE = "showConfirmationMultiDelete";
    const CMD_ADD_SERVICE_OPTION = "addServiceOption";
    const CMD_DELETE_SERVICE_OPTIONS = "deleteServiceOptions";
    const CMD_CHECK_SAVE = "checkSaveServiceOptions";
    const CMD_SAVE = "saveServiceOptions";
    const CMD_CANCEL = "cancel";

    /**
     * @var \ilRoomSetupConfigGUI
     */
    protected $parent_object;

    /**
     * @var ilPluginActions
     */
    protected $actions;

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var \ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $g_tpl;

    /**
     * @var TableProcessor
     */
    protected $table_processor;

    /**
     * @var ilToolbar
     */
    protected $g_toolbar;

    public function __construct(
        \ilRoomSetupConfigGUI $parent_object,
        RoomSetup\ilPluginActions $actions,
        \Closure $txt,
        RoomSetup\TableProcessing\TableProcessor $table_processor
    ) {
        global $DIC;

        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->g_toolbar = $DIC->toolbar();

        $this->parent_object = $parent_object;
        $this->actions = $actions;
        $this->txt = $txt;
        $this->table_processor = $table_processor;
    }

    /**
     * Required function of ILIAS forwardCommand system
     * Delegate function to use according to forwarded command
     * @throws Exception if coammnd is unknwon
     */
    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd(self::CMD_SHOW_SERVICE_OPTIONS);

        switch ($cmd) {
            case self::CMD_SHOW_SERVICE_OPTIONS:
                $this->showServiceOptions();
                break;
            case self::CMD_ADD_SERVICE_OPTION:
                $this->addServiceOption();
                break;
            case self::CMD_CHECK_SAVE:
                $this->checkSave();
                break;
            case self::CMD_SAVE:
                $this->saveServiceOptions();
                break;
            case self::CMD_SHOW_CONFIRMATION_MULTI_DELETE:
                $this->showConfirmationMultiDelete();
                break;
            case self::CMD_DELETE_SERVICE_OPTIONS:
                $this->deleteServiceOptions();
                break;
            case self::CMD_CANCEL:
                $this->cancel();
                break;
            default:
                throw new Exception(__METHOD__ . " unkown command " . $cmd);
        }
    }

    /**
     * Set toolbar buttons for new entries
     * @return void
     */
    protected function setToolbar()
    {
        $this->g_toolbar->setFormAction($this->g_ctrl->getFormAction($this, self::CMD_SHOW_SERVICE_OPTIONS));
        $this->g_toolbar->setCloseFormTag(false);
        $this->g_toolbar->addFormButton($this->txt("add_entry"), self::CMD_ADD_SERVICE_OPTION);
    }

    /**
     * Get the options table
     */
    protected function getTable() : RoomSetup\ServiceOptions\ilServiceOptionsTableGUI
    {
        $table = new RoomSetup\ServiceOptions\ilServiceOptionsTableGUI($this, $this->txt, self::CMD_SHOW_SERVICE_OPTIONS);
        $table->addCommandButton(self::CMD_CHECK_SAVE, $this->txt("save"));
        $table->addMulticommand(self::CMD_SHOW_CONFIRMATION_MULTI_DELETE, $this->txt("delete"));
        $table->setRowTemplate("tpl.service_options_row.html", $this->actions->getPlugin()->getDirectory());
        $table->determineOffsetAndOrder();
        $table->determineLimit();
        return $table;
    }

    /**
     * Shows all available service options
     * @param array | null	$options
     * @return void
     */
    protected function showServiceOptions(array $options = null)
    {
        $this->setToolbar();
        $table = $this->getTable();
        $order_field = $table->getOrderField();
        $order_direction = $table->getOrderDirection();
        $offset = (int) $table->getOffset();
        $limit = (int) $table->getLimit();

        if (is_null($options)) {
            $options = $this->createProcessingArray($this->actions->getAllServiceOptions($offset, $limit, $order_field, $order_direction));
        }

        $table->setMaxCount($this->actions->getAllServiceOptionsCount());
        $table->setData($options);

        $this->g_tpl->setContent($table->getHtml());
    }

    /**
     * Adds a new service option according to user input
     * @return void
     */
    protected function addServiceOption()
    {
        $this->setToolbar();
        $table = $this->getTable();

        $post_service_options = $this->getProcessingOptionsFromPost();
        $new_option = $this->createProcessingArray(array($this->actions->getEmptyServiceOption()));
        $data = array_merge($new_option, $post_service_options);

        $table->setMaxCount($this->actions->getAllServiceOptionsCount() + 1);
        $table->setData($data);

        $this->g_tpl->setContent($table->getHtml());
    }

    /**
     * Check the service options could be saved.
     * If not shows confirmation form.
     * @return void
     */
    protected function checkSave()
    {
        $post_service_options = $this->getProcessingOptionsFromPost();
        $name_changed_records = array_filter($post_service_options, function ($record) {
            if ($record["old_title"] != "" && $record["object"]->getName() != $record["old_title"]) {
                return $record;
            }
        });

        $cnt_changed_records = count($name_changed_records);

        if ($cnt_changed_records > 0) {
            if ($cnt_changed_records > 1) {
                $confirmation = $this->getConfirmationForm(self::CMD_SAVE, $this->txt("confirm_rename_multi"));
            } else {
                $confirmation = $this->getConfirmationForm(self::CMD_SAVE, $this->txt("confirm_rename_single"));
            }

            foreach ($name_changed_records as $record) {
                $confirmation->addItem("item_text", "", sprintf($this->txt("confirm_rename_text"), $record["old_title"], $record["object"]->getName()));
            }

            $confirmation->addHiddenItem("service_options", base64_encode(serialize($post_service_options)));

            $confirmation->setCancel($this->txt("cancel"), self::CMD_CANCEL);
            $confirmation->setConfirm($this->txt("update"), self::CMD_SAVE);

            $this->g_tpl->setContent($confirmation->getHtml());
        } else {
            $saved_service_options = $this->save($post_service_options);
            $this->showServiceOptions($saved_service_options);
        }
    }

    /**
     * Save edited service options
     * @return void
     */
    protected function saveServiceOptions()
    {
        $post = $_POST;
        $post_service_options = unserialize(base64_decode($post["service_options"]));
        $saved_service_options = $this->save($post_service_options);
        $this->showServiceOptions();
    }

    /**
     * Update or create new Service option
     * @param mixed[] 	$service_options
     *
     * @return mixed[]
     */
    protected function save(array $service_options) : array
    {
        return $this->table_processor->process(
            $service_options,
            array(RoomSetup\TableProcessing\TableProcessor::ACTION_SAVE
        )
        );
    }

    /**
     * Cancel saving or delete and show actual state
     * @return void
     */
    protected function cancel()
    {
        $this->showServiceOptions();
    }

    /**
     * Show the confirmation form for multi delete
     * @return void
     */
    protected function showConfirmationMultiDelete()
    {
        $post_service_options = $this->getProcessingOptionsFromPost();
        $delete_records = array_filter($post_service_options, function ($record) {
            if ($record["delete"]) {
                return $record;
            }
        });

        $cnt_delete_records = count($delete_records);

        if ($cnt_delete_records > 0) {
            if ($cnt_delete_records > 1) {
                $confirmation = $this->getConfirmationForm(self::CMD_SAVE, $this->txt("confirm_delete_multi"));
            } else {
                $confirmation = $this->getConfirmationForm(self::CMD_SAVE, $this->txt("confirm_delete_single"));
            }

            foreach ($delete_records as $record) {
                $confirmation->addItem("delete_item[]", "", $record["object"]->getName());
            }

            $confirmation->setConfirm($this->txt("delete"), self::CMD_DELETE_SERVICE_OPTIONS);
            $confirmation->setCancel($this->txt("cancel"), self::CMD_CANCEL);

            $confirmation->addHiddenItem("service_options", base64_encode(serialize($post_service_options)));
            $this->g_tpl->setContent($confirmation->getHTML());
        } else {
            $this->showServiceOptions($post_service_options);
        }
    }

    /**
     * Delete single confirmed service options
     * @return void
     */
    protected function deleteServiceOptions()
    {
        $post = $_POST;
        $service_options = unserialize(base64_decode($post["service_options"]));

        $service_options = $this->removeEmptyServiceOptions($service_options);
        $saved_service_options = $this->table_processor->process(
            $service_options,
            array(RoomSetup\TableProcessing\TableProcessor::ACTION_DELETE)
        );

        $this->showServiceOptions();
        \ilUtil::sendInfo($this->actions->getPlugin()->txt("delete_successfull"));
    }

    /**
     * Filter ServiceOptions that are tagged with 'delete' and with id -1
     */
    protected function removeEmptyServiceOptions(array $options) : array
    {
        return array_filter($options, function ($option) {
            if ($option['delete'] && $option['object']->getId() == -1) {
                return false;
            }
            return true;
        });
    }

    /**
     * Get instance of confirmation form
     */
    protected function getConfirmationForm(string $cmd, string $header_text) : \ilConfirmationGUI
    {
        require_once "./Services/Utilities/classes/class.ilConfirmationGUI.php";
        $confirmation = new \ilConfirmationGUI();
        $confirmation->setFormAction($this->g_ctrl->getFormAction($this, $cmd));
        $confirmation->setHeaderText($header_text);

        return $confirmation;
    }

    /**
     * Translate code to lang value
     */
    protected function txt(string $code) : string
    {
        $txt = $this->txt;
        return $txt($code);
    }

    /**
     * Get service options for processing from post
     * @return mixed[] | []
     */
    protected function getProcessingOptionsFromPost() : array
    {
        $ret = array();
        $post = $_POST;

        $del_array = array();
        if ($post["to_delete_ids"] && count($post["to_delete_ids"]) > 0) {
            $del_array = $post["to_delete_ids"];
        }

        foreach ($this->getOptionsFromPost() as $key => $service_option) {
            $ret[$key] = array("object" => $service_option, "errors" => array());
            $ret[$key]["delete"] = array_key_exists($key, $del_array);
            $ret[$key]["old_title"] = $post["old_title"][$key];
        }

        return $ret;
    }

    /**
     * Get options from post
     * @return Options[] | []
     */
    protected function getOptionsFromPost() : array
    {
        $ret = array();
        $post = $_POST;

        if ($post[RoomSetup\ilPluginActions::F_SERVICE_OPTION_NAME] && count($post[RoomSetup\ilPluginActions::F_SERVICE_OPTION_NAME]) > 0) {
            foreach ($post[RoomSetup\ilPluginActions::F_SERVICE_OPTION_NAME] as $key => $value) {
                $ret[$key] = $this->actions->getServiceOptionFor(
                    (int) $post["hidden_id"][$key],
                    $value,
                    (bool) $post[RoomSetup\ilPluginActions::F_SERVICE_OPTION_ACTIVE][$key]
                );
            }
        }

        return $ret;
    }

    /**
     * Create an array according to processing needed form
     * @param ServiceOption[] | [] $service_options
     * @return mixed[] | []
     */
    protected function createProcessingArray(array $service_options) : array
    {
        $ret = array();

        foreach ($service_options as $service_option) {
            $ret[] = array("object" => $service_option, "delete" => false, "errors" => array(), "message" => array());
        }

        return $ret;
    }
}

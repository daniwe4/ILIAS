<?php
use CaT\Plugins\CourseClassification\Options;
use CaT\Plugins\CourseClassification\TableProcessing\TableProcessor;

/**
 * Baseclass to configure the options for course classification
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilOptionsGUI
{
    const CMD_SHOW_OPTIONS = "showOptions";
    const CMD_SAVE_OPTIONS = "saveOptions";
    const CMD_DELETE_OPTIONS = "deleteOptions";
    const CMD_ADD_ENTRY = "addEntry";
    const CMD_SHOW_CONFIRMATION_MULTI_DELETE = "showConfirmationMultiDelete";
    const CMD_CANCEL = "cancel";

    /**
     * @var ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var ilToolbarGUI
     */
    protected $g_toolbar;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $g_tpl;

    /**
     * @var ilCourseClassificationConfigGUI
     */
    protected $parent;

    /**
     * @var TableProcessor
     */
    protected $table_processor;

    public function __construct(ilCourseClassificationConfigGUI $parent, Options\ilActions $actions, TableProcessor $table_processor, $type)
    {
        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->g_toolbar = $DIC->toolbar();
        $this->g_tpl = $DIC->ui()->mainTemplate();

        $this->parent = $parent;
        $this->table_processor = $table_processor;
        $this->actions = $actions;
        $this->type = $type;
    }

    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd(self::CMD_SHOW_OPTIONS);

        switch ($cmd) {
            case self::CMD_SHOW_OPTIONS:
                $options = $this->actions->getTableData();
                $process_data = $this->createProcessingArray($options);
                $this->showOptions($process_data);
                break;
            case self::CMD_ADD_ENTRY:
                $this->addEntry();
                break;
            case self::CMD_SAVE_OPTIONS:
                $this->saveOptions();
                break;
            case self::CMD_SHOW_CONFIRMATION_MULTI_DELETE:
                $this->showConfirmationMultiDelete();
                break;
            case self::CMD_DELETE_OPTIONS:
                $this->deleteOptions();
                break;
            case self::CMD_CANCEL:
                $this->cancel();
                break;
            default:
                throw new Exception("Unknown command " . $cmd);
        }
    }

    /**
     * Show available options
     *
     * @param Options[] | [] $options
     *
     * @return null
     */
    protected function showOptions(array $options)
    {
        $this->setToolbar();
        $table = $this->getTableGUI();
        $table->setData($this->sortOptions($options));
        $table->addCommandButton(self::CMD_SAVE_OPTIONS, $this->actions->getPlugin()->txt("save"));
        $table->addMultiCommand(self::CMD_SHOW_CONFIRMATION_MULTI_DELETE, $this->actions->getPlugin()->txt("delete"));
        $this->g_tpl->setContent($table->getHtml());
    }

    /**
     * Sort option entries according to caption for presentation.
     *
     * @return	array
     */
    protected function sortOptions(array $options)
    {
        uasort(
            $options,
            function ($one, $two) {
                return strcasecmp($one['option']->getCaption(), $two['option']->getCaption());
            }
        );
        return  $options;
    }

    /**
     * Get the apropriate table gui to render options.
     *
     * @return Options\ilOptionsTableGUI
     */
    protected function getTableGUI()
    {
        return new Options\ilOptionsTableGUI($this, $this->actions, $this->type);
    }

    /**
     * Add a new entry in current table
     *
     * @return null
     */
    protected function addEntry()
    {
        $new_option = $this->actions->getNewOption();
        $new_option = $this->createProcessingArray(array($new_option));
        $options = $this->getProcessingOptionsFromPost();
        $show_options = array_merge($options, $new_option);
        $this->showOptions($show_options);
    }

    /**
     * Save new and editet options
     *
     * @return null
     */
    protected function saveOptions()
    {
        $options = $this->getProcessingOptionsFromPost();
        $options = $this->table_processor->process($options, array(TableProcessor::ACTION_SAVE));
        $this->showOptions($options);
    }

    /**
     * Save new and editet options
     *
     * @return null
     */
    protected function deleteOptions()
    {
        $post = $_POST;
        $options = unserialize(base64_decode($post['option']));
        $processed_options = $this->table_processor->process($options, array(TableProcessor::ACTION_DELETE));
        $this->showOptions($processed_options);

        if (count($options) > count($processed_options)) {
            \ilUtil::sendInfo($this->actions->getPlugin()->txt("delete_successfull"));
        }
    }

    /**
     * Cancel saving or delete and show actual state
     *
     * @return null
     */
    protected function cancel()
    {
        $post = $_POST;
        $post_option = unserialize(base64_decode($post["option"]));
        $this->showOptions($post_option);
    }

    /**
     * Set the toolbar entries
     *
     * @return null
     */
    protected function setToolbar()
    {
        $this->g_ctrl->setParameter($this, "type", $this->type);
        $this->g_toolbar->setFormAction($this->g_ctrl->getFormAction($this, "showContent"));
        $this->g_toolbar->setCloseFormTag(false);
        $this->g_ctrl->setParameter($this, "type", null);

        include_once "Services/Form/classes/class.ilNumberInputGUI.php";
        $this->g_toolbar->addFormButton($this->actions->getPlugin()->txt("xacc_add_entry"), self::CMD_ADD_ENTRY);
    }

    /**
     * Create an array according to processing needed form
     *
     * @param Option[] | [] $options
     *
     * @return mixed[] | []
     */
    protected function createProcessingArray(array $options)
    {
        $ret = array();

        foreach ($options as $option) {
            $ret[] = array("option" => $option, "delete" => false, "errors" => array(), "message" => array());
        }

        return $ret;
    }

    /**
     * Show the confirmation form for multi delete
     *
     * @return null
     */
    protected function showConfirmationMultiDelete()
    {
        $post_option = $this->getProcessingOptionsFromPost();
        $delete_records = array_filter($post_option, function ($record) {
            if ($record["delete"]) {
                return $record;
            }
        });

        $cnt_delete_records = count($delete_records);

        if ($cnt_delete_records > 0) {
            if ($cnt_changed_records > 1) {
                $confirmation = $this->getConfirmationForm(self::CMD_SAVE_OPTIONS, $this->actions->getPlugin()->txt("confirm_delete_multi"));
            } else {
                $confirmation = $this->getConfirmationForm(self::CMD_SAVE_OPTIONS, $this->actions->getPlugin()->txt("confirm_delete_single"));
            }

            foreach ($delete_records as $record) {
                $confirmation->addItem("delete_item[]", "", $record["option"]->getCaption());
            }

            $confirmation->setConfirm($this->actions->getPlugin()->txt("delete"), self::CMD_DELETE_OPTIONS);
            $confirmation->setCancel($this->actions->getPlugin()->txt("cancel"), self::CMD_CANCEL);

            $confirmation->addHiddenItem("option", base64_encode(serialize($post_option)));
            $this->g_tpl->setContent($confirmation->getHTML());
        } else {
            $this->showOptions($post_option);
        }
    }

    /**
     * Get instance of confirmation form
     *
     * @param string 	$cmd
     * @param string 	$header_text
     *
     * @return \ilConfirmationGUI
     */
    protected function getConfirmationForm($cmd, $header_text)
    {
        require_once "./Services/Utilities/classes/class.ilConfirmationGUI.php";
        $confirmation = new \ilConfirmationGUI();
        $this->g_ctrl->setParameter($this, "type", $this->type);
        $confirmation->setFormAction($this->g_ctrl->getFormAction($this, $cmd));
        $confirmation->setHeaderText($header_text);

        return $confirmation;
    }

    /**
     * Get option for processing from post
     *
     * @return mixed[] | []
     */
    protected function getProcessingOptionsFromPost()
    {
        $ret = array();
        $post = $_POST;

        $del_array = array();
        if ($post["to_delete_ids"] && count($post["to_delete_ids"]) > 0) {
            $del_array = $post["to_delete_ids"];
        }

        foreach ($this->getOptionsFromPost() as $key => $option) {
            $ret[$key] = array("option" => $option, "errors" => array(), "message" => []);
            $ret[$key]["delete"] = in_array($key, $del_array);
        }

        return $ret;
    }

    /**
     * Get options from post
     *
     * @return Options[] | []
     */
    protected function getOptionsFromPost()
    {
        $ret = array();
        $post = $_POST;

        if ($post["caption"] && count($post["caption"]) > 0) {
            foreach ($post["caption"] as $key => $value) {
                $ret[$key] = new Options\Option((int) $post["hidden_id"][$key], $value);
            }
        }

        return $ret;
    }
}

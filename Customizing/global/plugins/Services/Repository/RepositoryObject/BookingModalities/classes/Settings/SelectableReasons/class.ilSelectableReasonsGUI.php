<?php

use CaT\Plugins\BookingModalities\ilActions;
use CaT\Plugins\BookingModalities\Settings\SelectableReasons\ilSelectableReasonsTableGUI;
use CaT\Plugins\BookingModalities\TableProcessing\TableProcessor;

/**
 * Configuration GUI for selectable reasons
 *
 * @author Stefan Hecken 	<stefan.hecken@cocnepts-and-training.de>
 */
class ilSelectableReasonsGUI
{
    const CMD_SHOW_REASONS = "showReasons";
    const CMD_SAVE_REASONS = "saveReasons";
    const CMD_ADD_LINES = "addLines";
    const CMD_CONFIRM_DELETE = "confirmDelete";
    const CMD_DELETE_REASONS = "deleteReasons";

    const F_NEW_LINES = "f_new_line";

    const F_HIDDEN_IDS = "f_hidden_ids";
    const F_DELETE_IDS = "f_delete_ids";
    const F_REASON = "f_reason";
    const F_ACTIVE = "f_active";

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $g_tpl;

    /**
     * @var ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var ilToolbar
     */
    protected $g_toolbar;

    /**
     * @var ilBookingModalitiesConfigGUI
     */
    protected $parent;

    /**
     * @var ilActions
     */
    protected $actions;

    /**
     * @var TableProcessor
     */
    protected $table_processor;

    public function __construct(ilBookingModalitiesConfigGUI $parent, ilActions $actions, TableProcessor $table_processor)
    {
        global $DIC;
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->g_ctrl = $DIC->ctrl();
        $this->g_toolbar = $DIC->toolbar();

        $this->parent = $parent;
        $this->actions = $actions;
        $this->table_processor = $table_processor;
    }

    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd(self::CMD_SHOW_REASONS);

        switch ($cmd) {
            case self::CMD_SHOW_REASONS:
                $reasons = $this->actions->getSelectableReasons();
                $process_data = $this->createProcessingArray($reasons);
                $this->showReasons($process_data);
                break;
            case self::CMD_SAVE_REASONS:
                $this->saveReasons();
                break;
            case self::CMD_ADD_LINES:
                $this->addLines();
                break;
            case self::CMD_CONFIRM_DELETE:
                $this->confirmDelete();
                break;
            case self::CMD_DELETE_REASONS:
                $this->deleteReasons();
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    /**
     * Show existing reasons
     *
     * @param mixed[] 	$process_data
     *
     * @return void
     */
    protected function showReasons($process_data)
    {
        $this->setToolbar();

        $table = new ilSelectableReasonsTableGUI($this, $this->actions);
        $process_data = $this->order($process_data, $table->getOrderDirection());
        $table->setData($process_data);
        $table->addCommandButton(self::CMD_SAVE_REASONS, $this->actions->getPlugin()->txt("save"));
        $table->addCommandButton(self::CMD_SHOW_REASONS, $this->actions->getPlugin()->txt("cancel"));
        $table->addMultiCommand(self::CMD_CONFIRM_DELETE, $this->actions->getPlugin()->txt("delete"));

        $this->g_tpl->setContent($table->getHtml());
    }


    protected function order(array $process_data, $order_direction)
    {
        $order_mod = 1;
        switch ($order_direction) {
            case 'asc':
                $order_mod = 1;
                break;
            case 'desc':
                $order_mod = -1;
                break;
        }
        uasort(
            $process_data,
            function ($one, $two) use ($order_mod) {
                return $order_mod * strcasecmp($one['object']->getReason(), $two['object']->getReason());
            }
        );
        return $process_data;
    }

    /**
     * Save edited reasons
     *
     * @return void
     */
    protected function saveReasons()
    {
        $process_data = $this->getProcessingReasonsFromPost();
        $process_data = $this->table_processor->process($process_data, array(TableProcessor::ACTION_SAVE));
        ilUtil::sendSuccess($this->actions->getPlugin()->txt("reasons_successful_saved"), true);
        $this->showReasons($process_data);
    }

    /**
     * Delete selected reasons
     *
     * @return null
     */
    protected function deleteReasons()
    {
        $post = $_POST;
        $process_data = unserialize(base64_decode($post['object']));
        $new_processed_options = $this->table_processor->process($process_data, array(TableProcessor::ACTION_DELETE));
        $this->showReasons($new_processed_options);

        if (count($process_data) > count($new_processed_options)) {
            \ilUtil::sendInfo($this->actions->getPlugin()->txt("delete_successfull"));
        }
    }

    /**
     * Adds new lines to table
     *
     * @return void
     */
    protected function addLines()
    {
        $post = $_POST;

        $new_reasons = array();
        $new_lines = (int) $post[self::F_NEW_LINES];
        for ($i = 0; $i < $new_lines; $i++) {
            $new_reasons[] = $this->actions->getNewSelectableReason();
        }

        $new_reasons = $this->createProcessingArray($new_reasons);
        $reasons = $this->getProcessingReasonsFromPost();
        $process_data = array_merge($reasons, $new_reasons);

        $this->showReasons($process_data);
    }

    /**
     * Create an array according to processing needed form
     *
     * @param SelectableResons[] 	$reasons
     *
     * @return mixed[] | []
     */
    protected function createProcessingArray(array $reasons)
    {
        $ret = array();

        foreach ($reasons as $reason) {
            $ret[] = array("object" => $reason, "delete" => false, "errors" => array(), "message" => array());
        }

        return $ret;
    }

    /**
     * Get reasons for processing from post
     *
     * @return mixed[] | []
     */
    protected function getProcessingReasonsFromPost()
    {
        $ret = array();
        $post = $_POST;

        $del_array = array();
        if ($post[self::F_DELETE_IDS] && count($post[self::F_DELETE_IDS]) > 0) {
            $del_array = $post[self::F_DELETE_IDS];
        }

        foreach ($this->getOptionsFromPost($post) as $key => $reason) {
            $ret[$key] = array("object" => $reason, "errors" => array());
            $ret[$key]["delete"] = in_array($key, $del_array);
        }

        return $ret;
    }

    /**
     * Get reasons from post
     *
     * @param sting[] 	$post
     *
     * @return SelectableReason[] | []
     */
    protected function getOptionsFromPost($post)
    {
        $ret = array();

        $hidden_ids = $post[self::F_HIDDEN_IDS];
        $reasons = $post[self::F_REASON];
        $actives = $post[self::F_ACTIVE];

        if ($hidden_ids && count($hidden_ids) > 0) {
            foreach ($hidden_ids as $key => $value) {
                $ret[$key] = $this->actions->getSelectableReason((int) $value, $reasons[$key], (bool) $actives[$key]);
            }
        }

        return $ret;
    }

    /**
     * Show the confirmation form for multi delete
     *
     * @return null
     */
    protected function confirmDelete()
    {
        $process_data = $this->getProcessingReasonsFromPost();
        $delete_reasons = array_filter($process_data, function ($reason) {
            if ($reason["delete"]) {
                return $reason;
            }
        });

        $cnt_delete_reasons = count($delete_reasons);

        if ($cnt_delete_reasons > 0) {
            $header = $this->actions->getPlugin()->txt("confirm_delete_single");
            if ($cnt_delete_reasons > 1) {
                $header = $this->actions->getPlugin()->txt("confirm_delete_multi");
            }

            $confirmation = $this->getConfirmationForm($header);
            foreach ($delete_reasons as $record) {
                $confirmation->addItem("delete_item[]", "", $record["object"]->getReason());
            }

            $confirmation->setConfirm($this->actions->getPlugin()->txt("delete"), self::CMD_DELETE_REASONS);
            $confirmation->setCancel($this->actions->getPlugin()->txt("cancel"), self::CMD_SHOW_REASONS);

            $confirmation->addHiddenItem("object", base64_encode(serialize($process_data)));
            $this->g_tpl->setContent($confirmation->getHTML());
        } else {
            $this->showReasons($process_data);
        }
    }

    /**
     * Get instance of confirmation form
     *
     * @param string 	$header_text
     *
     * @return \ilConfirmationGUI
     */
    protected function getConfirmationForm($header_text)
    {
        require_once "./Services/Utilities/classes/class.ilConfirmationGUI.php";
        $confirmation = new \ilConfirmationGUI($header_text);
        $confirmation->setHeaderText($header_text);
        $confirmation->setFormAction($this->g_ctrl->getFormAction($this));

        return $confirmation;
    }

    /**
     * Set the toolbar entries
     *
     * @return null
     */
    protected function setToolbar()
    {
        $this->g_toolbar->setFormAction($this->g_ctrl->getFormAction($this, self::CMD_SHOW_REASONS));
        $this->g_toolbar->setCloseFormTag(false);

        include_once "Services/Form/classes/class.ilTextInputGUI.php";
        $ni = new ilTextInputGUI("", self::F_NEW_LINES);
        $ni->setValue(1);

        $this->g_toolbar->addInputItem($ni);
        $this->g_toolbar->addFormButton($this->actions->getPlugin()->txt("add_entry"), self::CMD_ADD_LINES);
    }
}

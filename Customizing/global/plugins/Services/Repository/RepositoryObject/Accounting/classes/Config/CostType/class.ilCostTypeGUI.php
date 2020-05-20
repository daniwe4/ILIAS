<?php
declare(strict_types=1);

use \CaT\Plugins\Accounting\ilPluginActions;
use \CaT\Plugins\Accounting\TableProcessing\TableProcessor;
use \CaT\Plugins\Accounting\Config\CostType\ilCostTypeTableGUI;
use \CaT\Plugins\Accounting\Config\CostType\CostType;

/**
 * Plugin configuration base gui for costtypes.
 *
 * @author Daniel Weise	<daniel.weise@concepts-and-training.de>
 */
class ilCostTypeGUI
{
    const CMD_SHOW_COSTTYPE = "showCosttypes";
    const CMD_SHOW_CONFIRMATION_EDIT = "showConfirmationEdit";
    const CMD_SHOW_CONFIRMATION_MULTI_DELETE = "showConfirmationMultiDelete";
    const CMD_ADD_COSTTYPE = "addCosttypes";
    const CMD_DELETE_COSTTYPE = "deleteCosttypes";
    const CMD_CHECK_SAVE = "checkCosttypes";
    const CMD_SAVE = "saveEntry";
    const CMD_CANCEL = "cancel";

    /**
     * @var ilPluginActions
     */
    protected $actions;

    /**
     * @var Closure
     */
    protected $txt;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;

    /**
     * @var TableProcessor
     */
    protected $table_processor;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var string
     */
    protected $plugin_path;

    public function __construct(
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
        ilToolbarGUI $toolbar,
        string $plugin_path,
        ilPluginActions $actions,
        \Closure $txt,
        TableProcessor $table_processor
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->toolbar = $toolbar;
        $this->plugin_path = $plugin_path;

        $this->actions = $actions;
        $this->txt = $txt;
        $this->table_processor = $table_processor;
    }

    /**
     * Required function of ILIAS forwardCommand system
     * Delegate function to use according to forwarded command
     *
     * @throws Exception if coammnd is unknwon
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd(self::CMD_SHOW_COSTTYPE);

        switch ($cmd) {
            case self::CMD_SHOW_COSTTYPE:
                $this->showCosttypes();
                break;
            case self::CMD_ADD_COSTTYPE:
                $this->addCosttype();
                break;
            case self::CMD_CHECK_SAVE:
                $this->checkSave();
                break;
            case self::CMD_SAVE:
                $this->saveCosttype();
                break;
            case self::CMD_SHOW_CONFIRMATION_MULTI_DELETE:
                $this->showConfirmationMultiDelete();
                break;
            case self::CMD_DELETE_COSTTYPE:
                $this->deleteCosttype();
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
     *
     * @return null
     */
    protected function setToolbar()
    {
        $this->toolbar->setFormAction($this->ctrl->getFormAction($this, self::CMD_SHOW_COSTTYPE));
        $this->toolbar->setCloseFormTag(false);
        $this->toolbar->addFormButton($this->txt("add_entry"), self::CMD_ADD_COSTTYPE);
    }

    /**
     * Show data as table
     *
     * @param mixed[] 	$data
     */
    protected function show(array $data)
    {
        $this->setToolbar();
        $table = new ilCostTypeTableGUI(
            $this,
            self::CMD_SHOW_COSTTYPE,
            $this->txt
        );
        $table->setRowTemplate("tpl.accounting_config_costtype.html", $this->plugin_path);
        $order_field = $table->getOrderField();
        $order_direction = $table->getOrderDirection();
        $data = $this->sortData($data, $order_field, $order_direction);
        $table->setData($data);
        $table->addCommandButton(self::CMD_CHECK_SAVE, $this->txt("save"));
        $table->addMulticommand(self::CMD_SHOW_CONFIRMATION_MULTI_DELETE, $this->txt("delete"));
        $this->tpl->setContent($table->getHtml());
    }

    /**
     * Sorts data for correct view in table
     *
     * @param mixed[] 	$data
     *
     * @return mixed[]
     */
    protected function sortData(array $data, string $order_field, string $order_direction) : array
    {
        $sorted_data = $data;

        if ($order_field == "label") {
            usort($sorted_data, function ($a, $b) {
                return strcmp($a["object"]->getLabel(), $b["object"]->getLabel());
            });
        }

        if ($order_field == "value") {
            usort($sorted_data, function ($a, $b) {
                return strcmp($a["object"]->getValue(), $b["object"]->getValue());
            });
        }

        if ($order_field == "is_active") {
            usort($sorted_data, function ($a, $b) {
                return strcmp((string) $a["object"]->getActive(), (string) $b["object"]->getActive());
            });
        }

        if ($order_direction == "desc") {
            $sorted_data = array_reverse($sorted_data);
        }

        return $sorted_data;
    }

    protected function showCosttypes()
    {
        $data = $this->createProcessingArray($this->actions->readCostType());
        $this->show($data);
    }

    protected function addCosttype()
    {
        $post_costtype = $this->getProcessingOptionsFromPost();
        $new_option = $this->createProcessingArray(array($this->actions->getEmptyCostType()));
        $data = array_merge($post_costtype, $new_option);
        $this->show($data);
    }

    protected function checkSave()
    {
        $post_costtype = $this->getProcessingOptionsFromPost();

        $label_changed_record = array_filter($post_costtype, function ($record) {
            if ($record["old_label"] != "" && $record["object"]->getLabel() != $record["old_label"]) {
                return $record;
            }
        });

        $value_changed_record = array_filter($post_costtype, function ($record) {
            if ($record["old_value"] != "" && $record["object"]->getValue() != $record["old_value"]) {
                return $record;
            }
        });

        $cnt_changed_records = count($label_changed_record) + count($value_changed_record);
        
        if ($cnt_changed_records > 0) {
            if ($cnt_changed_records > 1) {
                $confirmation = $this->getConfirmationForm(self::CMD_SAVE, $this->txt("confirm_rename_multi_cost"));
            } else {
                $confirmation = $this->getConfirmationForm(self::CMD_SAVE, $this->txt("confirm_rename_single_cost"));
            }

            foreach ($label_changed_record as $record) {
                $confirmation->addItem("item_text", "", sprintf($this->txt("confirm_rename_text"), $record["old_label"], $record["object"]->getLabel()));
            }

            foreach ($value_changed_record as $record) {
                $confirmation->addItem("item_text", "", sprintf($this->txt("confirm_rename_text"), $record["old_value"], $record["object"]->getValue()));
            }

            $confirmation->addHiddenItem("costtype", base64_encode(serialize($post_costtype)));

            $confirmation->setCancel($this->txt("cancel"), self::CMD_CANCEL);
            $confirmation->setConfirm($this->txt("update"), self::CMD_SAVE);

            $this->tpl->setContent($confirmation->getHtml());
        } else {
            $saved_costtype = $this->save($post_costtype);
            $this->show($saved_costtype);
        }
    }

    protected function saveCosttype()
    {
        $post = $_POST;
        $post_costtype = unserialize(base64_decode($post["costtype"]));
        $saved_costtype = $this->save($post_costtype);
        $this->show($saved_costtype);
    }

    /**
     * Update or create new Service option
     *
     * @param mixed[] 	$costtype
     * @return mixed[]
     */
    protected function save(array $costtype) : array
    {
        return $this->table_processor->process($costtype, array(TableProcessor::ACTION_SAVE));
    }

    protected function cancel()
    {
        $post = $_POST;
        $post_costtype = unserialize(base64_decode($post["costtype"]));
        $this->show($post_costtype);
    }

    protected function showConfirmationMultiDelete()
    {
        $post_costtype = $this->getProcessingOptionsFromPost();
        $delete_records = array_filter(
            $post_costtype,
            function ($record) {
                if ($record["delete"]) {
                    return true;
                }

                return false;
            }
        );

        $cnt_delete_records = count($delete_records);

        if ($cnt_delete_records > 0) {
            if ($cnt_delete_records > 1) {
                $confirmation = $this->getConfirmationForm(self::CMD_SAVE, $this->txt("confirm_delete_multi"));
            } else {
                $confirmation = $this->getConfirmationForm(self::CMD_SAVE, $this->txt("confirm_delete_single"));
            }

            foreach ($delete_records as $record) {
                $confirmation->addItem("delete_item[]", "", $record["object"]->getLabel());
            }

            $confirmation->setConfirm($this->txt("delete"), self::CMD_DELETE_COSTTYPE);
            $confirmation->setCancel($this->txt("cancel"), self::CMD_CANCEL);

            $confirmation->addHiddenItem("costtype", base64_encode(serialize($post_costtype)));
            $this->tpl->setContent($confirmation->getHTML());
        } else {
            $this->show($post_costtype);
        }
    }

    protected function deleteCosttype()
    {
        $post = $_POST;
        $costtypes = unserialize(base64_decode($post["costtype"]));
        $error = false;

        foreach ($costtypes as &$costtype) {
            $flag = $costtype['delete'];
            $ct = $costtype['object'];
            if ($flag && $this->actions->hasCosttypeRelationships($ct->getId())) {
                $costtype['delete'] = false;
                $error = true;
            }
        }

        $saved_costtypes = $this->table_processor->process($costtypes, array(TableProcessor::ACTION_DELETE));
        $this->show($saved_costtypes);
        if ($error) {
            \ilUtil::sendFailure($this->txt("delete_error_dependencies"));
            return;
        }
        \ilUtil::sendInfo($this->txt("delete_successfull"));
    }

    protected function getConfirmationForm(string $cmd, string $header_text) : \ilConfirmationGUI
    {
        $confirmation = new \ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl->getFormAction($this, $cmd));
        $confirmation->setHeaderText($header_text);

        return $confirmation;
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }

    /**
     * Get service options for processing from post
     *
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

        foreach ($this->getOptionsFromPost() as $key => $costtype) {
            $ret[$key] = array("object" => $costtype, "errors" => array(), "message" => []);
            $ret[$key]["delete"] = array_key_exists($key, $del_array);
            $ret[$key]["old_label"] = $post["old_label"][$key];
            $ret[$key]["old_value"] = $post["old_value"][$key];
        }

        return $ret;
    }

    /**
     * Get options from post
     *
     * @return Options[] | []
     */
    protected function getOptionsFromPost() : array
    {
        $ret = array();
        $post = $_POST;

        if ($post[ilPluginActions::F_COSTTYPE_LABEL] && count($post[ilPluginActions::F_COSTTYPE_LABEL]) > 0) {
            foreach ($post[ilPluginActions::F_COSTTYPE_LABEL] as $key => $label) {
                $ret[$key] = $this->actions->getCostType(
                    (int) $post["hidden_id"][$key],
                    trim($post[ilPluginActions::F_COSTTYPE_VALUE][$key]),
                    $post[ilPluginActions::F_COSTTYPE_LABEL][$key],
                    (bool) $post[ilPluginActions::F_COSTTYPE_ACTIVE][$key]
                );
            }
        }

        return $ret;
    }

    /**
     * Create an array according to processing needed form
     *
     * @param CostType[] | [] $costtype
     *
     * @return mixed[] | []
     */
    protected function createProcessingArray(array $costtypes) : array
    {
        $ret = array();

        foreach ($costtypes as $costtype) {
            $ret[] = array("object" => $costtype, "delete" => false, "errors" => array(), "message" => array());
        }
        return $ret;
    }
}

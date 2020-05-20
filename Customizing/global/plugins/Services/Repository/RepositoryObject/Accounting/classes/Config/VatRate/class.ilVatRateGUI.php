<?php

declare(strict_types=1);

use \CaT\Plugins\Accounting;
use \CaT\Plugins\Accounting\TableProcessing\TableProcessor;

/**
 * Plugin configuration base gui for vatrates.
 *
 * @author Daniel Weise	<daniel.weise@concepts-and-training.de>
 */
class ilVatRateGUI
{
    const CMD_SHOW_VATRATE = "showVatrates";
    const CMD_SHOW_CONFIRMATION_EDIT = "showConfirmationEdit";
    const CMD_SHOW_CONFIRMATION_MULTI_DELETE = "showConfirmationMultiDelete";
    const CMD_ADD_VATRATE = "addVatrates";
    const CMD_DELETE_VATRATE = "deleteVatrates";
    const CMD_CHECK_SAVE = "checkVatrates";
    const CMD_SAVE = "saveEntry";
    const CMD_CANCEL = "cancel";

    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;
    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;
    /**
     * @var ilTree
     */
    protected $tree;
    /**
     * @var ilAppEventHandler
     */
    protected $event_handler;
    /**
     * @var Closure
     */
    protected $txt;
    /**
     * @var string
     */
    protected $plugin_path;
    /**
     * @var Accounting\ilPluginActions
     */
    protected $actions;
    /**
     * @var TableProcessor
     */
    protected $table_processor;

    public function __construct(
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
        ilToolbarGUI $toolbar,
        ilTree $tree,
        ilAppEventHandler $event_handler,
        \Closure $txt,
        string $plugin_path,
        Accounting\ilPluginActions $actions,
        TableProcessor $table_processor
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->toolbar = $toolbar;
        $this->tree = $tree;
        $this->event_handler = $event_handler;

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
        $cmd = $this->ctrl->getCmd(self::CMD_SHOW_VATRATE);

        switch ($cmd) {
            case self::CMD_SHOW_VATRATE:
                $this->showVatrates();
                break;
            case self::CMD_ADD_VATRATE:
                $this->addVatrate();
                break;
            case self::CMD_CHECK_SAVE:
                $this->checkSave();
                break;
            case self::CMD_SAVE:
                $this->saveVatrate();
                break;
            case self::CMD_SHOW_CONFIRMATION_MULTI_DELETE:
                $this->showConfirmationMultiDelete();
                break;
            case self::CMD_DELETE_VATRATE:
                $this->deleteVatrate();
                break;
            case self::CMD_CANCEL:
                $this->cancel();
                break;
            default:
                throw new Exception(__METHOD__ . " unkown command " . $cmd);
        }
    }

    protected function setToolbar()
    {
        $this->toolbar->setFormAction($this->ctrl->getFormAction($this));
        $this->toolbar->setCloseFormTag(false);
        $this->toolbar->addFormButton($this->txt("add_entry"), self::CMD_ADD_VATRATE);
    }

    /**
     * @param mixed[] 	$data
     */
    protected function show(array $data)
    {
        $this->setToolbar();
        $table = new Accounting\Config\VatRate\ilVatRateTableGUI(
            $this,
            self::CMD_SHOW_VATRATE,
            $this->txt
        );
        $table->setRowTemplate("tpl.accounting_config_vatrate.html", $this->plugin_path);
        $order_field = $table->getOrderField();
        $order_direction = $table->getOrderDirection();
        $data = $this->sortData($data, $order_field, $order_direction);
        $table->setData($data);
        $table->addCommandButton(self::CMD_CHECK_SAVE, $this->txt("save"));
        $table->addMulticommand(self::CMD_SHOW_CONFIRMATION_MULTI_DELETE, $this->txt("delete"));
        $this->tpl->setContent($table->getHtml());
    }

    /**
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

    protected function showVatrates()
    {
        $data = $this->createProcessingArray($this->actions->readVatRate());
        $this->show($data);
    }

    protected function addVatrate()
    {
        $post_vatrate = $this->getProcessingOptionsFromPost();
        $new_option = $this->createProcessingArray(array($this->actions->getEmptyCostType()));
        $data = array_merge($post_vatrate, $new_option);
        $this->show($data);
    }

    protected function checkSave()
    {
        $post_vatrate = $this->getProcessingOptionsFromPost();

        $label_changed_record = array_filter($post_vatrate, function ($record) {
            if (
                $record["old_label"] != "" &&
                $record["object"]->getLabel() != $record["old_label"] &&
                $record["object"]->getLabel() != ""
            ) {
                return true;
            }

            return false;
        });

        $value_changed_record = array_filter($post_vatrate, function ($record) {
            if (
                $record["old_value"] != "" &&
                $record["object"]->getValue() != $record["old_value"] &&
                $record["object"]->getValue() != ""
            ) {
                return true;
            }

            return false;
        });

        $cnt_changed_records = count($label_changed_record) + count($value_changed_record);

        if ($cnt_changed_records > 0) {
            if ($cnt_changed_records > 1) {
                $confirmation = $this->getConfirmationForm(self::CMD_SAVE, $this->txt("confirm_rename_multi_vat"));
            } else {
                $confirmation = $this->getConfirmationForm(self::CMD_SAVE, $this->txt("confirm_rename_single_vat"));
            }

            foreach ($label_changed_record as $record) {
                $confirmation->addItem("item_text", "", sprintf($this->txt("confirm_rename_text"), $record["old_label"], $record["object"]->getLabel()));
            }

            foreach ($value_changed_record as $record) {
                $confirmation->addItem(
                    "item_text",
                    "",
                    sprintf(
                        $this->txt("confirm_rename_text"),
                        number_format((float) $record["old_value"], 2, ',', ''),
                        number_format((float) $record["object"]->getValue(), 2, ',', '')
                    )
                );
            }

            $confirmation->addHiddenItem("vatrate", base64_encode(serialize($post_vatrate)));

            $confirmation->setCancel($this->txt("cancel"), self::CMD_CANCEL);
            $confirmation->setConfirm($this->txt("update"), self::CMD_SAVE);

            $this->tpl->setContent($confirmation->getHtml());
        } else {
            $saved_vatrate = $this->save($post_vatrate);
            $this->show($saved_vatrate);
        }
    }

    protected function saveVatrate()
    {
        $post = $_POST;
        $post_vatrate = unserialize(base64_decode($post["vatrate"]));
        $saved_vatrate = $this->save($post_vatrate);
        $this->show($saved_vatrate);
    }

    protected function updateVatRateEvent()
    {
        $e = array("xaccs" => $this->getAllXacc());
        $this->event_handler->raise("Plugin/Accounting", "updateVatRate", $e);
    }

    /**
     * Get all xacc objects below ilias root
     *
     * @return \ilObjAccounting[]
     */
    protected function getAllXacc() : array
    {
        return $this->getAllChildrenOfByType((int) ROOT_FOLDER_ID, "xacc");
    }

    /**
     * @return \ilObjAccounting[]
     */
    protected function getAllChildrenOfByType(int $ref_id, string $search_type) : array
    {
        $childs = $this->tree->getSubTree(
            $this->tree->getNodeData($ref_id),
            false,
            $search_type
        );
        $ret = array();

        foreach ($childs as $child) {
            $ret[] = \ilObjectFactory::getInstanceByRefId($child);
        }

        return $ret;
    }

    /**
     * @param mixed[] 	$vatrate
     * @return mixed[]
     */
    protected function save(array $vatrate)
    {
        $records = $this->table_processor->process($vatrate, array(TableProcessor::ACTION_SAVE));
        $this->updateVatRateEvent();

        return $records;
    }

    protected function cancel()
    {
        $this->ctrl->redirect($this, self::CMD_SHOW_VATRATE);
    }

    protected function showConfirmationMultiDelete()
    {
        $post_vatrate = $this->getProcessingOptionsFromPost();
        $delete_records = array_filter(
            $post_vatrate,
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

            $confirmation->setConfirm($this->txt("delete"), self::CMD_DELETE_VATRATE);
            $confirmation->setCancel($this->txt("cancel"), self::CMD_CANCEL);

            $confirmation->addHiddenItem("vatrate", base64_encode(serialize($post_vatrate)));
            $this->tpl->setContent($confirmation->getHTML());
        } else {
            $this->show($post_vatrate);
        }
    }

    protected function deleteVatrate()
    {
        $post = $_POST;
        $vatrates = unserialize(base64_decode($post["vatrate"]));
        $error = false;

        foreach ($vatrates as &$vatrate) {
            $flag = $vatrate['delete'];
            $vr = $vatrate['object'];
            if ($flag && $this->actions->hasVatrateRelationships($vr->getId())) {
                $vatrate['delete'] = false;
                $error = true;
            }
        }

        $saved_vatrate = $this->table_processor->process($vatrates, array(TableProcessor::ACTION_DELETE));
        $this->show($saved_vatrate);
        if ($error) {
            \ilUtil::sendFailure($this->txt("delete_error_dependencies"));
            return;
        }
        \ilUtil::sendInfo($this->txt("delete_successfull"));
    }

    protected function getConfirmationForm(string $cmd, string $header_text) : ilConfirmationGUI
    {
        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl->getFormAction($this, $cmd));
        $confirmation->setHeaderText($header_text);

        return $confirmation;
    }

    /**
     * Translate code to lang value
     *
     * @param string 	$code
     *
     * @return string
     */
    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }

    /**
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

        foreach ($this->getOptionsFromPost() as $key => $vatrate) {
            $ret[$key] = array("object" => $vatrate, "errors" => array(), "message" => []);
            $ret[$key]["delete"] = array_key_exists($key, $del_array);
            $ret[$key]["old_label"] = $post["old_label"][$key];
            $ret[$key]["old_value"] = $post["old_value"][$key];
        }

        return $ret;
    }

    /**
     * @return Options[] | []
     */
    protected function getOptionsFromPost() : array
    {
        $ret = array();
        $post = $_POST;

        if ($post[Accounting\ilPluginActions::F_VATRATE_LABEL] && count($post[Accounting\ilPluginActions::F_VATRATE_LABEL]) > 0) {
            foreach ($post[Accounting\ilPluginActions::F_VATRATE_LABEL] as $key => $label) {
                $ret[$key] = $this->actions->getVatRate(
                    (int) $post["hidden_id"][$key],
                    str_replace(',', '.', trim($post[Accounting\ilPluginActions::F_VATRATE_VALUE][$key])),
                    $post[Accounting\ilPluginActions::F_VATRATE_LABEL][$key],
                    (bool) $post[Accounting\ilPluginActions::F_VATRATE_ACTIVE][$key]
                );
            }
        }

        return $ret;
    }

    /**
     * Create an array according to processing needed form
     *
     * @param Accounting\Config\VatRate\VatRate[]
     *
     * @return mixed[] | []
     */
    protected function createProcessingArray(array $vatrates) : array
    {
        $ret = array();

        foreach ($vatrates as $vatrate) {
            $ret[] = array("object" => $vatrate, "delete" => false, "errors" => array(), "message" => array());
        }
        return $ret;
    }
}

<?php

use CaT\Plugins\Accounting\Data\Data;
use CaT\Plugins\Accounting\Data\Export\ExportData;
use \CaT\Plugins\Accounting\ilObjectActions;
use CaT\Plugins\Accounting\Settings\Settings;
use CaT\Plugins\Accounting\Config\CostType;
use CaT\Plugins\Accounting\Config\VatRate;

/**
 * GUI for Data
 *
 * @author Daniel Weise 	<daniel.weise@concepts-and-training.de>
 */
class ilDataGUI extends TMSTableParentGUI
{
    const CMD_SHOW_CONTENT = "showContent";
    const CMD_ACCOUNTING = "accounting";
    const CMD_SAVE_ENTRY = "saveEntry";
    const CMD_ADD_ENTRY = "addEntries";
    const CMD_CONFIRM_DELETE = "confirmDelete";
    const CMD_DELETE_ENTRY = "deleteEntry";
    const CMD_CONFIRM_FINISH = "confirmFinish";
    const CMD_FINISH = "finish";
    const CMD_CANCEL = "cancel";
    const CMD_EXCEL_EXPORT = "excelExport";
    const TAB_ACCOUNTING = "accounting";
    const F_BILL_DATE = "bill_date";
    const F_DATE_RELAY = "date_relay";

    const MAX_NEW_ENTRIES = 12;
    const CMD_VR_RATE = "vrValue";

    /**
     * @var $ilCtrl
     */
    protected $ctrl;
    /**
     * @var $ilTemplate
     */
    protected $tpl;
    /**
     * @var $ilToolbar
     */
    protected $toolbar;
    /**
     * @var ilAccess
     */
    protected $access;
    /**
     * @var Closure
     */
    protected $txt;
    /**
     * @var ilObjectActions
     */
    protected $object_actions;
    /**
     * @var CostType\DB
     */
    protected $cost_db;
    /**
     * @var VatRate\DB
     */
    protected $vat_db;
    /**
     * @var string
     */
    protected $plugin_folder;
    /**
     * @var string
     */
    protected $plugin_prefix;
    /**
     * @var ExportData
     */
    protected $list_exporter;
    /**
     * @var Settings
     */
    protected $settings;
    /**
     * @var string[]
     */
    protected $wrong_field_data;

    public function __construct(
        ilCtrl $ctrl,
        ilTemplate $tpl,
        ilToolbarGUI $toolbar,
        ilAccess $access,
        Closure $txt,
        ilObjectActions $object_actions,
        CostType\DB $cost_db,
        VatRate\DB $vat_db,
        string $plugin_folder,
        string $plugin_prefix,
        ExportData $list_exporter
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->toolbar = $toolbar;
        $this->access = $access;

        $this->txt = $txt;
        $this->object_actions = $object_actions;
        $this->cost_db = $cost_db;
        $this->vat_db = $vat_db;
        $this->plugin_folder = $plugin_folder;
        $this->plugin_prefix = $plugin_prefix;
        $this->list_exporter = $list_exporter;
        $this->settings = $this->object_actions->getObject()->getSettings();
    }

    /**
     * @throws Exception
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd(self::CMD_SHOW_CONTENT);
        $finalized = $this->settings->getFinalized();
        switch ($cmd) {
            case self::CMD_SHOW_CONTENT:
            case self::CMD_ACCOUNTING:
            case self::CMD_CANCEL:
                if ($finalized) {
                    $this->showFinalizedContent();
                    break;
                }
                $this->setToolbar();
                $this->showContent();
                break;
            case self::CMD_SAVE_ENTRY:
            case self::CMD_ADD_ENTRY:
                $this->setToolbar();
                $this->$cmd();
                break;
            case self::CMD_DELETE_ENTRY:
            case self::CMD_CONFIRM_DELETE:
            case self::CMD_CONFIRM_FINISH:
            case self::CMD_FINISH:
            case self::CMD_EXCEL_EXPORT:
            case self::CMD_VR_RATE:
                $this->$cmd();
                break;
            default:
                throw new \Exception(__METHOD__ . ": unkown command " . $cmd);
        }
    }

    /**
     * Get async the value of selected vatrate
     * Is will be async called by jS
     *
     * @return void
     */
    protected function vrValue()
    {
        $get = $_GET;
        $id = (int) $get["vr_id"];
        $vr_value = (float) $this->vat_db->getVatRateValueById($id);
        $ff = array("vr_value" => $vr_value);
        echo json_encode($ff);
        exit;
    }

    /**
     * Handle the showContent command
     * @throws Exception
     */
    protected function showContent(array $data = null, array $errors = array())
    {
        if ($data == null) {
            $data = $this->getDBEntries();
        }
        $this->setJavaScript();
        $this->renderTable($data, $errors);
    }

    /**
     * Shows the content after using the finilazed command
     * @throws Exception
     */
    public function showFinalizedContent()
    {
        \ilUtil::sendInfo($this->txt("xacc_content_is_finalized"), true);
        $this->setFinalizedToolbar();
        $data = $this->getDBEntries();
        $this->renderTable($data);
    }

    /**
     * Render the table gui
     *
     * @param Data[] 	$data
     * @throws Exception
     */
    protected function renderTable(array $data, array $errors = array())
    {
        // used for the sum field at the end of the table
        array_push($data, array("sum" => "summary"));
        $this->tpl->addCss($this->plugin_folder . "/templates/design.css");

        $table = $this->getTMSTableGUI();
        $this->configurateTable($table);

        $table->setData($data);
        $this->setWrongFieldData($errors);
        $req_tpl = new \ilTemplate("tpl.accounting_req_hint.html", true, true, $this->plugin_folder);
        $req_tpl->setVariable("TXT_REQUIRED", $this->txt("required_field"));

        $json_tpl = new \ilTemplate("tpl.jquery_async_link.html", true, true, $this->plugin_folder);
        $json_tpl->setVariable("ACCOUNTING_JSON", $this->ctrl->getLinkTarget($this, self::CMD_VR_RATE, "", true));

        $this->tpl->setContent(
            $table->getHtml() .
            $req_tpl->get() .
            $json_tpl->get()
        );
    }

    /**
     * Create the structure of the table here
     */
    protected function configurateTable($table)
    {
        $ref_id = $this->object_actions->getObject()->getRefId();

        $required_indicator = '<span class="asterisk">*</span>';
        $table->setTitle($this->txt("accounting"));
        $table->setExternalSegmentation(false);
        $table->setShowRowsSelector(false);
        $table->setRowTemplate("tpl.accounting_row.html", $this->plugin_folder);
        $table->addColumn("", "", "1", true);
        $table->addColumn($this->txt("xacc_costtype") . $required_indicator);
        $table->addColumn($this->txt("xacc_bill_date"));
        $table->addColumn($this->txt("xacc_nr"));
        $table->addColumn($this->txt("xacc_date_relay"));
        $table->addColumn($this->txt("xacc_issuer"));
        $table->addColumn($this->txt("xacc_bill_comment"));
        $table->addColumn($this->txt("xacc_amount") . $required_indicator);
        $table->addColumn($this->txt("xacc_tax") . $required_indicator);
        $table->addColumn($this->txt("xacc_gross"));
        if (!$this->settings->getFinalized()) {
            if ($this->access->checkAccess("edit_entries", "", $ref_id) ||
               $this->access->checkAccess("add_entries", "", $ref_id)) {
                $table->addCommandButton("saveEntry", $this->txt("xacc_save"));
            }
            if ($this->access->checkAccess("delete_entries", "", $ref_id)) {
                $table->addMultiCommand("confirmDelete", $this->txt("delete"));
            }
        }
    }

    /**
     * Get the closure is table should be filled with
     *
     * @return Closure
     */
    protected function fillRow()
    {
        return function ($table, $data) {
            $ref_id = $this->object_actions->getObject()->getRefId();

            if (is_array($data)) {
                $ni = new \ilTextInputGUI("", "summe");
                $ni->setValue(number_format($this->getSumAmount(), 2, ",", ""));
                $ni->setInlineStyle("text-align: right;background-color: white");
                $ni->setDisabled(true);
                $table->getTemplate()->setVariable("AMOUNT", $ni->render());

                $ni = new \ilTextInputGUI("", "sum_gross");
                $ni->setValue(number_format($this->getSumGross(), 2, ",", ""));
                $ni->setInlineStyle("text-align: right;background-color: white");
                $ni->setDisabled(true);
                $table->getTemplate()->setVariable("GROSS", $ni->render());
            } else {
                $pos = $data->getPos();
                $add = $this->access->checkAccess("add_entries", "", $ref_id);
                $edit = $this->access->checkAccess("edit_entries", "", $ref_id);
                $finalized = $this->settings->getFinalized();
                $costtype = "costtype" . $pos;
                $bill_date = "bill_date" . $pos;
                $nr = "nr" . $pos;
                $date_relay = "date_relay" . $pos;
                $issuer = "issuer" . $pos;
                $bill_comment = "bill_comment" . $pos;
                $amount = "amount" . $pos;
                $tax = "tax" . $pos;
                $gross = "gross" . $pos;

                if ($finalized || !$edit) {
                    $frozen = true;
                }
                if ($data->getId() == -1 && $add) {
                    $frozen = false;
                }

                if (!$finalized && $this->access->checkAccess("delete_entries", "", $ref_id)) {
                    $table->getTemplate()->setVariable("ID", $pos);
                    $table->getTemplate()->setVariable("POST_VAR", ilObjectActions::F_DATA_CHK);
                }

                $table->getTemplate()->setVariable("HIDDEN_ID", $data->getId());

                $si = new \ilSelectInputGUI("", $costtype);

                // Get all active options
                $options = $this->cost_db->getSelectionArray();

                // Get the label for deactivated entries and add it to the active entries
                if ($data->getCostType() && $data->getId() != -1) {
                    $a = array($data->getCostType() => $data->getCTLabel());
                    $options = $a + $options;
                }

                uasort($options, function ($a, $b) {
                    return strcmp($a, $b);
                });

                $options = array(null => $this->txt("please_select")) + $options;
                $si->setOptions($options);


                $si->setValue($data->getCostType());
                $si->setDisabled($frozen);
                $table->getTemplate()->setVariable("COSTTYPE", $si->render());
                if ($error_data = $this->getWrongFieldDataByField($costtype)) {
                    $this->showWrongField($table, "costtype", $error_data);
                }

                $bi = new \ilDateTimeInputGUI("", $bill_date);
                if ($data->getBillDate()) {
                    $bi->setDate($data->getBillDate());
                }
                $bi->setDisabled($frozen);
                $table->getTemplate()->setVariable("BILL_DATE", $bi->render());
                if ($error_data = $this->getWrongFieldDataByField($bill_date)) {
                    $this->showWrongField($table, "bill_date", $error_data);
                }

                $ti = new \ilTextInputGUI("", "nr" . $pos);
                $ti->setValue($data->getNr());
                $ti->setDisabled($frozen);
                $table->getTemplate()->setVariable("NR", $ti->render());
                if ($error_data = $this->getWrongFieldDataByField($nr)) {
                    $this->showWrongField($table, "nr", $error_data);
                }

                $bi = new \ilDateTimeInputGUI("", $date_relay);
                if ($data->getDateRelay()) {
                    $bi->setDate($data->getDateRelay());
                }
                $bi->setDisabled($frozen);
                $table->getTemplate()->setVariable("DATE_RELAY", $bi->render());
                if ($error_data = $this->getWrongFieldDataByField($date_relay)) {
                    $this->showWrongField($table, "date_relay", $error_data);
                }

                $ti = new \ilTextInputGUI("", $issuer);
                $ti->setValue($data->getIssuer());
                $ti->setDisabled($frozen);
                $table->getTemplate()->setVariable("ISSUER", $ti->render());
                if ($error_data = $this->getWrongFieldDataByField($issuer)) {
                    $this->showWrongField($table, "issuer", $error_data);
                }

                $ti = new \ilTextAreaInputGUI("", $bill_comment);
                $ti->setValue($data->getBillComment());
                $ti->setDisabled($frozen);
                $ti->insert($table->getTemplate());
                if ($error_data = $this->getWrongFieldDataByField($bill_comment)) {
                    $this->showWrongField($table, "bill_comment", $error_data);
                }

                $ni = new \ilTextInputGUI("", $amount);
                $ni->setValue(number_format($data->getAmount(), 2, ',', ''));
                $ni->setInlineStyle("text-align: right;background-color: white");
                $this->sumAmount($data->getAmount());
                $ni->setDisabled($frozen);
                $table->getTemplate()->setVariable("AMOUNT", $ni->render());
                if ($error_data = $this->getWrongFieldDataByField($amount)) {
                    $this->showWrongField($table, "amount", $error_data);
                }

                $si = new \ilSelectInputGUI("", $tax);

                // Get all active options
                $options = $this->vat_db->getSelectionArray();

                // Get the label for deactivated entries and add it to the active entries
                if ($data->getVatrate() && $data->getId() != -1) {
                    $a = array($data->getVatrate() => $data->getVRLabel());
                    $options = $a + $options;
                }

                $options = array(null => $this->txt("please_select")) + $options;
                $si->setOptions($options);

                $si->setValue($data->getVatrate());
                $si->setDisabled($frozen);
                $table->getTemplate()->setVariable("TAX", $si->render());
                if ($error_data = $this->getWrongFieldDataByField($tax)) {
                    $this->showWrongField($table, "tax", $error_data);
                }

                $this->sumGross($data->getGross());
                $ni = new \ilTextInputGUI("", $gross);
                $ni->setValue(number_format($data->getGross(), 2, ",", ""));
                $ni->setInlineStyle("text-align: right;background-color: white");
                $ni->setDisabled(true);
                $table->getTemplate()->setVariable("GROSS", $ni->render());
            }
        };
    }

    /**
     * Check if the field is in the wrong fields array
     *
     * @param string 		$field 		fieldname
     * @return string | null
     */
    protected function getWrongFieldDataByField($field)
    {
        $wrong = $this->getWrongFieldData();
        if ($wrong && array_key_exists($field, $wrong)) {
            return $wrong[$field];
        }
        return null;
    }

    /**
     * Set an array with wrong input fields
     *
     * @param string[] 		$value 		wrong fields
     */
    protected function setWrongFieldData(array $value)
    {
        $this->wrong_field_data = $value;
    }

    /**
     * Display an alert immage with short text
     * below the field with wrong input data.
     *
     * @param 	string 		$field 		field with wrong input data
     * @param 	string[]	$msgs 		short alert messages
     */
    protected function showWrongField($table, $field, array $msgs)
    {
        $table->getTemplate()->setCurrentBlock($field . "_alert");
        $table->getTemplate()->setVariable("IMG_ALERT", \ilUtil::getImagePath("icon_alert.svg"));
        $table->getTemplate()->setVariable("ALT_ALERT", $this->txt("alert"));
        $table->getTemplate()->setVariable("TXT_ALERT", implode("<br>", $this->translateArray($msgs)));
        $table->getTemplate()->parseCurrentBlock();
    }

    /**
     * Creates a sum of all amounts
     *
     * @param float 	$value
     */
    protected function sumAmount($value)
    {
        $this->sum_amount += $value;
    }

    /**
     * Get the calculated amount sum
     *
     * @return float
     */
    protected function getSumAmount()
    {
        return $this->sum_amount;
    }

    /**
     * Creates a sum of all gross
     *
     * @param float 	$value
     */
    protected function sumGross($value)
    {
        $this->sum_gross += $value;
    }

    /**
     * Get the calculated gross sum
     *
     * @return float
     */
    protected function getSumGross()
    {
        return $this->sum_gross;
    }

    /**
     * Translate each string from the $msgs array to
     * the actual language.
     *
     * @param 	string[] 	$msgs
     */
    protected function translateArray(array $msgs)
    {
        return array_map(function ($lng) {
            return $this->txt("xacc_" . $lng);
        }, $msgs);
    }

    /**
     * Get an wrong field array
     *
     * @return string[] 		wrong input fields array
     */
    protected function getWrongFieldData()
    {
        return $this->wrong_field_data;
    }

    /**
     * @inheritdoc
     */
    protected function tableCommand()
    {
        return self::CMD_SHOW_CONTENT;
    }

    /**
     * @inheritdoc
     */
    protected function tableId()
    {
        return get_class($this);
    }

    /**
     * Create the toolbar to add an editable amount of data rows
     */
    protected function setToolbar()
    {
        $ref_id = $this->object_actions->getObject()->getRefId();
        $this->toolbar->setFormAction($this->ctrl->getFormAction($this, "showContent"));
        $this->toolbar->setCloseFormTag(false);

        if ($this->access->checkAccess("add_entries", "", $ref_id)) {
            include_once "Services/Form/classes/class.ilTextInputGUI.php";
            $number = new ilTextInputGUI("", "addnum");
            $number->setSize(2);
            $number->setValue(1);
            $this->toolbar->addInputItem($number);
            $this->toolbar->addFormButton($this->txt("xacc_add_entry"), self::CMD_ADD_ENTRY);
        }
        if ($this->access->checkAccess("finalize_recording", "", $ref_id)) {
            $this->toolbar->addSeparator();
            $btn = ilButton::getInstance();
            $btn->setName(self::CMD_CONFIRM_FINISH);
            $btn->setCaption($this->plugin_prefix . "_xacc_finish");

            $this->toolbar->addButtonInstance($btn);
        }

        if ($this->object_actions->getParentCourse()) {
            $this->toolbar->addSeparator();
            $this->toolbar->addFormButton($this->txt("xacc_excel_export"), self::CMD_EXCEL_EXPORT);
        }
    }

    /**
     * Create the toolbar for finalized content
     */
    protected function setFinalizedToolbar()
    {
        $this->toolbar->setFormAction($this->ctrl->getFormAction($this, "showContent"));
        $this->toolbar->addFormButton($this->txt("xacc_excel_export"), self::CMD_EXCEL_EXPORT);
        $this->toolbar->addSeparator();
    }

    /**
     * Merge all entries to one array and render
     */
    protected function addEntries()
    {
        $this->setJavaScript();
        $ref_id = $this->object_actions->getObject()->getRefId();
        $entries = [];

        if (
            $this->access->checkAccess("add_entries", "", $ref_id) &&
            !$this->access->checkAccess("edit_entries", "", $ref_id)
        ) {
            $entries = $this->getDBEntries();
        }

        $entries = array_merge($entries, $this->getPostEntries());
        $new_entries = $this->getNewEntries(count($entries));

        $entries = array_merge($entries, $new_entries);
        $this->showContent($entries);
    }

    /**
     * Save entries with minimum one field isn´t empty
     */
    protected function saveEntry()
    {
        $ref_id = $this->object_actions->getObject()->getRefId();
        list($check_result, $issue_ids) = $this->checkUserEntries($_POST, $ref_id);

        $entries = [];
        if (
            $this->access->checkAccess("add_entries", "", $ref_id) &&
            !$this->access->checkAccess("edit_entries", "", $ref_id)
        ) {
            $entries = $this->getDBEntries();
        }
        $entries = array_merge($entries, $this->getPostEntries());

        foreach ($entries as $entry) {
            if (in_array($entry->getPos(), $issue_ids)) {
                continue;
            }

            if ($entry->getId() == -1) {
                $this->object_actions->insertData($entry);
            } else {
                $this->object_actions->updateData($entry);
            }
        }

        if (count($check_result) > 0) {
            $this->showContent($entries, $check_result);
            return;
        }

        $this->object_actions->updatedEvent();
        \ilUtil::sendSuccess($this->txt('xacc_entries_save'), true);
        $this->ctrl->redirect($this, self::CMD_SHOW_CONTENT);
    }

    /**
     * Checks the correctness of user entries
     *
     * @param 	array 	$post
     * @param 	int 	$ref_id
     * @return 	array 	$issues
     */
    public function checkUserEntries(array $post, $ref_id)
    {
        $issues = [];
        $issue_ids = [];
        $hidden_ids = $post["hidden_id"];
        if (count($hidden_ids) > 0) {
            foreach ($hidden_ids as $pos => $id) {
                $id = $post["hidden_id"][$pos];
                if ($this->access->checkAccess("add_entries", "", $ref_id)
                    && !$this->access->checkAccess("edit_entries", "", $ref_id)
                    && $id != -1
                ) {
                    continue;
                }

                $costtype = $post['costtype' . $pos];
                $amount = (float) $this->gerFloatToInternational($post['amount' . $pos]);
                $tax = $post['tax' . $pos];

                if ($costtype == "" || !is_string($costtype)) {
                    $issues['costtype' . $pos][] = "costtype_error";
                    $issue_ids[] = $pos;
                }
                if (!filter_var($amount, FILTER_VALIDATE_FLOAT)) {
                    $issues['amount' . $pos][] = "amount_error";
                    $issue_ids[] = $pos;
                }
                if (!filter_var($tax, FILTER_VALIDATE_INT)) {
                    $issues['tax' . $pos][] = "tax_error";
                    $issue_ids[] = $pos;
                }
            }
        }

        $issue_ids = array_unique($issue_ids);
        return [$issues, $issue_ids];
    }

    /**
     * Convert german type floats (, as decimal point) to international
     * (. as deciaml point).
     *
     * @param	string	$float_string
     * @return	string
     */
    public function gerFloatToInternational($float_string)
    {
        return str_replace(',', '.', $float_string);
    }

    /**
     * Handle the confirmFinish command
     */
    public function confirmFinish()
    {
        require_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl->getFormAction($this));
        $confirmation->setHeaderText($this->txt("xacc_finish_confirmation"));
        $confirmation->setConfirm($this->txt("xacc_confirm"), self::CMD_FINISH);
        $confirmation->setCancel($this->txt("xacc_cancel"), self::CMD_CANCEL);
        $this->tpl->setContent($confirmation->getHTML());
    }

    /**
     * Handle the finish command
     */
    public function finish()
    {
        $this->object_actions->finalize();
        $this->object_actions->updatedEvent();
        $this->ctrl->redirect($this, self::CMD_SHOW_CONTENT);
    }

    /**
     * Handle the confirmDelete command
     */
    public function confirmDelete()
    {
        $post = $_POST;

        if (!isset($post['data_chk'])) {
            \ilUtil::sendInfo($this->txt('xacc_no_entries_delete'), true);
            $this->ctrl->redirect($this, self::CMD_SHOW_CONTENT);
        }

        require_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
        $confirmation = new ilConfirmationGUI();

        foreach ($post['data_chk'] as $key => $value) {
            $confirmation->addHiddenItem('data_chk[]', $value);
        }
        foreach ($post['hidden_id'] as $key => $value) {
            $confirmation->addHiddenItem('hidden_id[]', $value);
        }

        $confirmation->setFormAction($this->ctrl->getFormAction($this));
        $confirmation->setHeaderText($this->txt("xacc_delete_confirmation"));
        $confirmation->setConfirm($this->txt("xacc_confirm"), self::CMD_DELETE_ENTRY);
        $confirmation->setCancel($this->txt("xacc_cancel"), self::CMD_CANCEL);
        $this->tpl->setContent($confirmation->getHTML());
    }

    /**
     * Delete checked entries
     */
    protected function deleteEntry()
    {
        $post = $_POST;
        foreach ($post['data_chk'] as $num) {
            $this->object_actions->deleteData((int) $post['hidden_id'][$num]);
        }
        $this->object_actions->updatedEvent();
        \ilUtil::sendSuccess($this->txt('xacc_entries_delete'), true);
        $this->ctrl->redirect($this, self::CMD_SHOW_CONTENT);
    }

    /**
     * Get entries from the database
     *
     * @return CostType[]
     */
    protected function getDBEntries()
    {
        return $this->object_actions->selectFor($this->object_actions->getObjId());
    }

    protected function getNewEntries(int $current_count)
    {
        $entries = [];
        $entries_to_add = $this->getAmountEntriesToAdd();
        if ($entries_to_add > self::MAX_NEW_ENTRIES || $entries_to_add < 1) {
            \ilUtil::sendInfo($this->txt("xacc_above_max_entries"), true);
            $entries_to_add = self::MAX_NEW_ENTRIES;
        }

        for ($i = 0; $i < $entries_to_add; $i++) {
            $entries[] = new Data(-1, $current_count);
            $current_count++;
        }
        return $entries;
    }

    protected function getPostEntries()
    {
        $entries = [];
        $post = $_POST;
        $hidden_ids = $post["hidden_id"];
        if (count($hidden_ids) > 0) {
            $ref_id = $this->object_actions->getObject()->getRefId();
            foreach ($hidden_ids as $pos => $id) {
                if ($this->access->checkAccess("add_entries", "", $ref_id)
                    && !$this->access->checkAccess("edit_entries", "", $ref_id)
                    && $id != -1
                ) {
                    continue;
                }

                $obj_id = $this->object_actions->getObjId();
                $costtype = $post['costtype' . $pos];
                $nr = $post['nr' . $pos];
                $issuer = $post['issuer' . $pos];
                $bill_comment = $post['bill_comment' . $pos];
                $amount = (float) $this->gerFloatToInternational($post['amount' . $pos]);
                $tax = (int) $post['tax' . $pos];
                if ($post['bill_date' . $pos] != null) {
                    $bill_date = new ilDate($post['bill_date' . $pos], IL_CAL_DATE);
                } else {
                    $bill_date = null;
                }
                if ($post['date_relay' . $pos] != null) {
                    $date_relay = new ilDate($post['date_relay' . $pos], IL_CAL_DATE);
                } else {
                    $date_relay = null;
                }

                $ct_label = "";
                $ct_value = "";
                if ($costtype != "") {
                    $ct_label = $this->cost_db->getCTLabel($costtype);
                    $ct_value = $this->cost_db->getCTValue($costtype);
                }

                $vr_label = "";
                $vr_value = 0;
                if ($tax != 0) {
                    $vr_label = $this->vat_db->getVRLabel($tax);
                    $vr_value = $this->vat_db->getVatRateValueById($tax);
                }

                array_push($entries, new Data(
                    (int) $id,
                    (int) $pos,
                    $obj_id,
                    (int) $costtype,
                    $bill_date,
                    $nr,
                    $date_relay,
                    $issuer,
                    $bill_comment,
                    (float) $amount,
                    $tax,
                    $ct_label,
                    $vr_label,
                    $ct_value,
                    $vr_value
                ));
            }
        }
        return $entries;
    }

    /**
     * Create an excel stylesheet and serve it for download
     */
    public function excelExport()
    {
        $crs = $this->object_actions->getParentCourse();

        if ($crs) {
            $crs_title = $crs->getTitle();
            $crs_date = $this->getCourseDate($crs);
            $export_data = $this->getExportData($this->object_actions->getObjId());

            $table_headers = [
                $this->txt("xacc_costtype"),
                $this->txt("xacc_ex_id"),
                $this->txt("xacc_bill_date"),
                $this->txt("xacc_nr"),
                $this->txt("xacc_date_relay"),
                $this->txt("xacc_issuer"),
                $this->txt("xacc_bill_comment"),
                $this->txt("xacc_amount"),
                $this->txt("xacc_xlsx_tax"),
                $this->txt("xacc_gross")
            ];

            $this->list_exporter->run($table_headers, $crs_title, $crs_date, $export_data);
        } else {
            \ilUtil::sendInfo($this->txt("no_parent_course_no_file"));
        }
    }

    /**
     * @return string[]
     */
    public function getExportData(int $obj_id) : array
    {
        $ret = [];
        $data = $this->object_actions->selectFor($obj_id);
        foreach ($data as $dat) {
            $gross = $dat->getAmount() * ((100 + $dat->getVRValue()) / 100);
            $gross = round($gross, 2);
            $tmp = [$dat->getCTLabel(),
                    $dat->getCTValue(),
                    $dat->getBillDate()->get(IL_CAL_FKT_DATE, "d.m.Y"),
                    $dat->getNr(),
                    $dat->getDateRelay()->get(IL_CAL_FKT_DATE, "d.m.Y"),
                    $dat->getIssuer(),
                    $dat->getBillComment(),
                    $dat->getAmount(),
                    $dat->getVRValue(),
                    $gross
                    ];
            array_push($ret, $tmp);
        }
        return $ret;
    }

    protected function getCourseDate(ilObjCourse $crs) : string
    {
        $start = "";
        $end = "";

        $x = $crs->getCourseStart();
        if ($x !== null) {
            $start = $x->get(IL_CAL_FKT_DATE, "d.m.Y");
        }

        $x = $crs->getCourseEnd();
        if ($x !== null) {
            $end = $x->get(IL_CAL_FKT_DATE, "d.m.Y");
        }

        return $start . " - " . $end;
    }

    protected function getAmountEntriesToAdd() : int
    {
        return $_POST['addnum'];
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }

    protected function setJavaScript()
    {
        iljQueryUtil::initjQuery();
        $this->tpl->addJavaScript($this->plugin_folder . "/js/sum_amount.js");
    }
}

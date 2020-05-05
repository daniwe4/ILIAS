<?php

use CaT\Plugins\CourseMember\LPOptions;
use CaT\Plugins\CourseMember\TableProcessing\TableProcessor;

require_once "Services/TMS/Table/TMSTableParentGUI.php";

/**
 * Base gui to view all existing lp options
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilLPOptionsGUI extends TMSTableParentGUI
{
    const CMD_SHOW_OPTIONS = "showOptions";
    const CMD_SAVE_OPTIONS = "saveOptions";
    const CMD_DELETE_CONFIRM = "deleteConfirm";
    const CMD_DELETE_OPTIONS = "deleteOptions";
    const CMD_ADD_NEW_ROWS = "addNewRows";

    const F_NEW_ROWS = "f_new_rows";
    const F_SERIALIZED_OPTIONS = "f_serialized_options";

    const F_DELETE_IDS = "to_delete_ids";
    const F_TITLE = "title";
    const F_ILIAS_LP = "ilias_lp";
    const F_ACTIVE = "active";
    const F_HIDDEN_ID = "hidden_id";
    const F_STANDARD = "standard";

    /**
     * @var ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $g_tpl;

    /**
     * @var ilToolbarGUI
     */
    protected $g_toolbar;

    /**
     * @var LPOptions\ilActions
     */
    protected $actions;

    /**
     * @var TableProcessor
     */
    protected $table_processor;

    public function __construct(
        ilCourseMemberConfigGUI $parent,
        LPOptions\ilActions $actions,
        TableProcessor $table_processor,
        \ilCtrl $ctrl,
        \ilGlobalTemplateInterface $tpl,
        \ilToolbarGUI $toolbar,
        \ilLanguage $lng
    ) {
        global $DIC;
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->toolbar = $toolbar;
        $this->lng = $lng;
        $this->lng->loadLanguageModule("trac");
        $this->actions = $actions;
        $this->table_processor = $table_processor;
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        $this->setToolbar();
        switch ($cmd) {
            case self::CMD_SHOW_OPTIONS:
                $this->showOptions();
                break;
            case self::CMD_ADD_NEW_ROWS:
                $this->addNewRows();
                break;
            case self::CMD_SAVE_OPTIONS:
                $this->saveOptions();
                break;
            case self::CMD_DELETE_CONFIRM:
                $this->deleteConfirm();
                break;
            case self::CMD_DELETE_OPTIONS:
                $this->deleteOptions();
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    /**
     * Show table to administrate lp options
     *
     * @return void
     */
    protected function showOptions()
    {
        $lp_options = $this->actions->getLPOptions();
        $processing = $this->createProcessing($lp_options);
        $this->showTable($processing);
    }

    /**
     * Adds amount of new rows to table
     *
     * @return void
     */
    protected function addNewRows()
    {
        $post = $_POST;
        $new_rows = (int) $post[self::F_NEW_ROWS];
        $processing = $this->getProcessingFrom($post);
        $id = 0;

        if ($new_rows > 0) {
            for ($i = 0; $i < $new_rows; $i++) {
                $id--;
                $lp_options[] = $this->actions->getEmptyLPOption($id);
            }

            $new_processing = $this->createProcessing($lp_options);
            $processing = array_merge($processing, $new_processing);
        }

        $this->showTable($processing);
    }

    /**
     * Save all valid options
     *
     * @return void
     */
    protected function saveOptions()
    {
        $post = $_POST;
        $processing = $this->getProcessingFrom($post);
        $processing = $this->table_processor->process($processing, array(TableProcessor::ACTION_SAVE));

        $this->showTable($processing);
    }

    /**
     * Show the confirmation form for multi delete
     *
     * @return void
     */
    protected function deleteConfirm()
    {
        $post = $_POST;
        $post_option = $this->getProcessingFrom($post);
        $delete_records = array_filter($post_option, function ($record) {
            if ($record["delete"]) {
                return $record;
            }
        });

        $cnt_delete_records = count($delete_records);

        if ($cnt_delete_records > 0) {
            if ($cnt_delete_records > 1) {
                $confirmation = $this->getConfirmationForm(self::CMD_SAVE_OPTIONS, $this->actions->getPlugin()->txt("confirm_delete_multi"));
            } else {
                $confirmation = $this->getConfirmationForm(self::CMD_SAVE_OPTIONS, $this->actions->getPlugin()->txt("confirm_delete_single"));
            }

            foreach ($delete_records as $record) {
                $confirmation->addItem("delete_item[]", "", $record["object"]->getTitle());
            }

            $confirmation->setConfirm($this->actions->getPlugin()->txt("delete"), self::CMD_DELETE_OPTIONS);
            $confirmation->setCancel($this->actions->getPlugin()->txt("cancel"), self::CMD_SHOW_OPTIONS);

            $confirmation->addHiddenItem(self::F_SERIALIZED_OPTIONS, base64_encode(serialize($post_option)));
            $this->tpl->setContent($confirmation->getHTML());
        } else {
            \ilUtil::sendInfo($this->actions->getPlugin()->txt("nothing_to_delete"));
            $this->showTable($post_option);
        }
    }

    /**
     * Deletes selected options
     *
     * @return void
     */
    protected function deleteOptions()
    {
        $post = $_POST;
        $options = unserialize(base64_decode($post[self::F_SERIALIZED_OPTIONS]));
        $processed_options = $this->table_processor->process($options, array(TableProcessor::ACTION_DELETE));
        $this->showTable($processed_options);

        if (count($options) > count($processed_options)) {
            \ilUtil::sendInfo($this->actions->getPlugin()->txt("delete_successfull"));
        }
    }

    /**
     * Dispalys tbale view lp opiotions
     *
     * @param array 	$processind
     *
     * @return void
     */
    protected function showTable(array $processing)
    {
        $table = $this->getTMSTableGUI();
        $this->configurateTable($table);
        $table->setData($processing);
        $table->setFormAction($this->ctrl->getFormAction($this));
        $table->addCommandButton(self::CMD_SAVE_OPTIONS, $this->txt("save"));
        $table->addCommandButton(self::CMD_SHOW_OPTIONS, $this->txt("cancel"));
        $table->addMultiCommand(self::CMD_DELETE_CONFIRM, $this->txt("delete"));

        $this->tpl->setContent($table->getHtml());
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
        $confirmation->setFormAction($this->ctrl->getFormAction($this, $cmd));
        $confirmation->setHeaderText($header_text);

        return $confirmation;
    }

    /**
     * Create the toolbar to add an editable amount of data rows
     *
     * @return void
     */
    protected function setToolbar($disable_finalize = true)
    {
        $this->toolbar->setFormAction($this->ctrl->getFormAction($this));
        $this->toolbar->setCloseFormTag(false);

        include_once "Services/Form/classes/class.ilTextInputGUI.php";
        $number = new ilTextInputGUI("", self::F_NEW_ROWS);
        $number->setSize(2);
        $number->setValue(1);
        $this->toolbar->addInputItem($number);
        $this->toolbar->addFormButton($this->txt("add_entries"), self::CMD_ADD_NEW_ROWS);
    }

    /**
     * Get option for processing from post
     *
     * @param array<int, string[]> 	$post
     *
     * @return mixed[] | []
     */
    protected function getProcessingFrom($post)
    {
        $del_array = array();
        if ($post[self::F_DELETE_IDS] && count($post[self::F_DELETE_IDS]) > 0) {
            $del_array = $post[self::F_DELETE_IDS];
        }

        $ret = array();
        foreach ($this->getObjectsFrom($post) as $key => $option) {
            $ret[$key] = array("object" => $option, "errors" => array(), "message" => array());
            $ret[$key]["delete"] = in_array($key, $del_array);
        }

        return $ret;
    }

    /**
     * Get options from post
     *
     * @param array<int, string[]> 	$post
     *
     * @return Options[] | []
     */
    protected function getObjectsFrom($post)
    {
        $ret = array();
        $standard_value = null;
        if (!is_null($post[self::F_STANDARD])) {
            $standard_value = (int) array_shift($post[self::F_STANDARD]);
        }

        if ($post[self::F_HIDDEN_ID] && count($post[self::F_HIDDEN_ID]) > 0) {
            foreach ($post[self::F_HIDDEN_ID] as $key => $id) {
                $title = $post[self::F_TITLE][$key];
                $ilias_lp = (int) $post[self::F_ILIAS_LP][$key];

                $active = false;
                if (isset($post[self::F_ACTIVE][$key]) && (int) $post[self::F_ACTIVE][$key] == 1) {
                    $active = true;
                }

                $standard = !is_null($standard_value) && $standard_value == $id;
                $ret[$id] = $this->actions->getLPOptionWith((int) $id, $title, $ilias_lp, $active, $standard);
            }
        }

        return $ret;
    }

    /**
     * Create an array according to processing needed form
     *
     * @param LPOption[] | [] $options
     *
     * @return mixed[] | []
     */
    protected function createProcessing(array $options)
    {
        $ret = array();

        foreach ($options as $option) {
            $ret[] = array("object" => $option, "delete" => false, "errors" => array(), "message" => array());
        }

        return $ret;
    }

    /**
     * Translates lang code to clear text
     *
     * @param string 	$code
     *
     * @return string
     */
    protected function txt($code)
    {
        return $this->actions->getPlugin()->txt($code);
    }

    protected function configurateTable(\ilTMSTableGUI $table)
    {
        $table->setRowTemplate("tpl.lp_option_row.html", $this->actions->getPlugin()->getDirectory());
        $table->setTitle($this->txt("tbl_course_member"));
        $table->setFormAction($this->ctrl->getFormAction($this));

        $table->addColumn("", "", "1", true);
        $table->addColumn($this->txt("title") . '<span class="asterisk"> *</span>', false);
        $table->addColumn($this->txt("ilias_lp") . '<span class="asterisk"> *</span>', false);
        $table->addColumn($this->txt("active"), false);
        $table->addColumn($this->txt("default"), false);
    }

    /**
     * @inheritdoc
     */
    protected function fillRow()
    {
        return function ($table, $a_set) {
            $object = $a_set["object"];
            $errors = $a_set["errors"];
            $message = $a_set["message"];

            $tpl = $table->getTemplate();

            $tpl->setVariable("POST_VAR", self::F_DELETE_IDS);
            $tpl->setVariable("ID", $object->getId());

            $tpl->setVariable("HIDDEN", self::F_HIDDEN_ID);
            $tpl->setVariable("HIDDEN_ID", $object->getId());

            require_once("Services/Form/classes/class.ilTextInputGUI.php");
            $ti = new \ilTextInputGUI("", self::F_TITLE . "[]");
            $ti->setValue($object->getTitle());
            $tpl->setVariable("TITLE", $ti->render());

            if (array_key_exists("title", $errors)) {
                $caption_errors = $errors["title"];
                $caption_errors = array_map(function ($err) {
                    return $this->txt($err);
                }, $caption_errors);
                $tpl->setCurrentBlock("title_alert");
                $tpl->setVariable("IMG_ALERT", \ilUtil::getImagePath("icon_alert.svg"));
                $tpl->setVariable("ALT_ALERT", $this->txt("alert"));
                $tpl->setVariable("TXT_ALERT", implode(",", $caption_errors));
                $tpl->parseCurrentBlock();
            }

            if (count($message) > 0) {
                $message = array_map(function ($mes) {
                    return $this->txt($mes);
                }, $message);

                $tpl->setCurrentBlock("message");
                $tpl->setVariable("MESSAGE_CSS_ROW", $this->css_row);
                $tpl->setVariable("MESSAGE", implode("<br />", $message));
                $tpl->parseCurrentBlock();
            }

            require_once("Services/Form/classes/class.ilSelectInputGUI.php");
            $si = new \ilSelectInputGUI("", self::F_ILIAS_LP . "[]");
            $si->setOptions($this->getILIASLPOptions());
            $si->setValue($object->getILIASLP());
            $tpl->setVariable("ILIAS_LP", $si->render());

            if (array_key_exists("ilias_lp", $errors)) {
                $caption_errors = $errors["ilias_lp"];
                $caption_errors = array_map(function ($err) {
                    return $this->txt($err);
                }, $caption_errors);
                $tpl->setCurrentBlock("ilias_lp_alert");
                $tpl->setVariable("IMG_ALERT", \ilUtil::getImagePath("icon_alert.svg"));
                $tpl->setVariable("ALT_ALERT", $this->txt("alert"));
                $tpl->setVariable("TXT_ALERT", implode(",", $caption_errors));
                $tpl->parseCurrentBlock();
            }

            $tpl->setVariable("ACTIVE", self::F_ACTIVE);
            if ($object->getActive()) {
                $tpl->touchBlock("checked");
            }

            $tpl->setVariable("STANDARD", self::F_STANDARD);
            if ($object->isStandard()) {
                $tpl->touchBlock("checked_default");
            }
        };
    }

    /**
     * Return options for ilias lp
     *
     * @return string[]
     */
    protected function getILIASLPOptions()
    {
        require_once("Services/Tracking/classes/class.ilLPStatus.php");
        return array(
            -1 => $this->txt("please_select"),
            \ilLPStatus::LP_STATUS_COMPLETED_NUM => $this->lng->txt(\ilLPStatus::LP_STATUS_COMPLETED),
            \ilLPStatus::LP_STATUS_FAILED_NUM => $this->lng->txt(\ilLPStatus::LP_STATUS_FAILED),
            \ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM => $this->lng->txt(\ilLPStatus::LP_STATUS_NOT_ATTEMPTED)
        );
    }

    /**
     * @inheritdoc
     */
    protected function tableCommand()
    {
        return self::CMD_SHOW_OPTIONS;
    }

    /**
     * @inheritdoc
     */
    protected function tableId()
    {
        return "lp_options";
    }
}

<?php

declare(strict_types=1);

use CaT\Plugins\Accounting\Config\Cancellation\Scale;
use CaT\Plugins\Accounting\TableProcessing\TableProcessor;

class ilScaleGUI extends TMSTableParentGUI
{
    const CMD_SHOW_SCALES = "showScales";
    const CMD_SAVE_SCALES = "saveScales";
    const CMD_CONFORM_DELETE = "confirmDelete";
    const CMD_DELETE_SCALES = "deleteScales";
    const CMD_ADD_SCALE = "addScalre";

    const C_ID = "id";
    const C_TO_DELETE = "to_delete";
    const C_SPAN_START = Scale\ScaleBackend::C_SPAN_START;
    const C_SPAN_END = Scale\ScaleBackend::C_SPAN_END;
    const C_PERCENT = Scale\ScaleBackend::C_PERCENT;

    const MAX_VALUE = 100;

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
     * @var Closure
     */
    protected $txt;
    /**
     * @var Scale\DB
     */
    protected $scale_db;
    /**
     * @var TableProcessor
     */
    protected $table_processor;
    /**
     * @var string
     */
    protected $plugin_path;

    public function __construct(
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
        ilToolbarGUI $toolbar,
        Closure $txt,
        Scale\DB $scale_db,
        TableProcessor $table_processor,
        string $plugin_path
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->toolbar = $toolbar;
        $this->txt = $txt;
        $this->scale_db = $scale_db;
        $this->table_processor = $table_processor;
        $this->plugin_path = $plugin_path;
    }

    /**
     * @inheritDoc
     * @throws Exception if cmd is not known
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_SHOW_SCALES:
                $this->setToolbar();
                $this->showScales();
                break;
            case self::CMD_SAVE_SCALES:
                $this->saveScales();
                break;
            case self::CMD_CONFORM_DELETE:
                $this->confirmDelete();
                break;
            case self::CMD_DELETE_SCALES:
                $this->deleteScales();
                break;
            case self::CMD_ADD_SCALE:
                $this->addScale();
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    protected function showScales(array $proc_array = null)
    {
        if (is_null($proc_array)) {
            $scales = $this->scale_db->getScales();
            $proc_array = $this->createProcessingArray($scales);
        }

        $table = $this->getTMSTableGUI();
        $table->setTitle($this->txt("scale_title"));
        $table->setRowTemplate("tpl.scales_row.html", $this->plugin_path);
        $table->setFormAction($this->ctrl->getFormAction($this));
        $table->setData($proc_array);

        $table->addColumn("", "", "1", true);
        $table->addColumn($this->txt(self::C_SPAN_START));
        $table->addColumn($this->txt(self::C_SPAN_END));
        $table->addColumn($this->txt(self::C_PERCENT));

        $table->addCommandButton(self::CMD_SAVE_SCALES, $this->txt("save"));
        $table->addCommandButton(self::CMD_SHOW_SCALES, $this->txt("cancel"));

        $table->addMultiCommand(self::CMD_CONFORM_DELETE, $this->txt("delete"));

        $this->tpl->setContent($table->getHTML());
    }

    protected function saveScales()
    {
        $post = $_POST;
        $scales = $this->getScaleObjectsFromPost($post);
        $proc_array = $this->createProcessingArray($scales);

        $proc_array = $this->table_processor->process($proc_array, [TableProcessor::ACTION_SAVE]);

        $errors = array_filter(
            $proc_array,
            function ($record) {
                return count($record["errors"]) > 0;
            }
        );

        if (count($errors) > 0) {
            $this->showScales($proc_array);
            return;
        }

        ilUtil::sendSuccess($this->txt("scale_sucessfull_saved"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW_SCALES);
    }

    protected function addScale()
    {
        $post = $_POST;
        $current = $this->getScaleObjectsFromPost($post);
        $scales = array_merge($current, [new Scale\Scale(-1, -1, -1, -1)]);
        $proc_array = $this->createProcessingArray($scales);
        $this->showScales($proc_array);
    }

    protected function confirmDelete()
    {
        $post = $_POST;
        $current = $this->getScaleObjectsFromPost($post);
        $proc_array = $this->createProcessingArrayForDelete($current, $post);

        if (count($proc_array) == 0) {
            ilUtil::sendInfo($this->txt("nothing_to_delete"), true);
            $this->ctrl->redirect($this, self::CMD_SHOW_SCALES);
        }

        $confirmation = new ilConfirmationGUI();
        foreach ($proc_array as $record) {
            /**
            * @var Scale\Scale $scale
            */
            $scale = $record["object"];
            $msg = sprintf($this->txt("del_scale_msd"), $scale->getSpanStart(), $scale->getSpanEnd(), $scale->getPercent());
            $confirmation->addItem("", "", $msg);
        }
        $confirmation->addHiddenItem(self::C_TO_DELETE, base64_encode(serialize($proc_array)));

        $confirmation->setFormAction($this->ctrl->getFormAction($this));
        $confirmation->setHeaderText($this->txt("delete_scales_confirm"));
        $confirmation->setConfirm($this->txt("xacc_confirm"), self::CMD_DELETE_SCALES);
        $confirmation->setCancel($this->txt("xacc_cancel"), self::CMD_SHOW_SCALES);
        $this->tpl->setContent($confirmation->getHTML());
    }

    protected function deleteScales()
    {
        $post = $_POST;

        $delete_items = unserialize(base64_decode($post[self::C_TO_DELETE]));
        $not_delete_items = $this->table_processor->process($delete_items, [TableProcessor::ACTION_DELETE]);

        if (count($not_delete_items) < count($delete_items)) {
            ilUtil::sendSuccess($this->txt("delete_successfull"));
        }

        $this->ctrl->redirect($this, self::CMD_SHOW_SCALES);
    }

    protected function setToolbar()
    {
        $this->toolbar->setFormAction($this->ctrl->getFormAction($this, self::CMD_SHOW_SCALES));
        $this->toolbar->setCloseFormTag(false);

        $button = ilSubmitButton::getInstance();
        $button->setCaption($this->txt("add_entry"), false);
        $button->setCommand(self::CMD_ADD_SCALE);
        $this->toolbar->addButtonInstance($button);
    }

    protected function getScaleObjectsFromPost(array $post) : array
    {
        $ret = [];
        if (
            !array_key_exists(self::C_ID, $post) ||
            count($post[self::C_ID]) == 0
        ) {
            return $ret;
        }

        foreach ($post[self::C_ID] as $key => $id) {
            $spant_start = (int) $post[self::C_SPAN_START][$key];
            $spant_end = (int) $post[self::C_SPAN_END][$key];
            $percent = (int) $post[self::C_PERCENT][$key];

            $ret[] = new Scale\Scale(
                (int) $id,
                $spant_start,
                $spant_end,
                $percent
            );
        }

        return $ret;
    }

    /**
     * @param Scale\Scale[] 	$scales
     * @param string[] 	$post
     *
     * @return mixed[]
     */
    protected function createProcessingArray(array $scales, array $post = [])
    {
        $ret = [];
        $to_delete = [];

        if (array_key_exists(self::C_TO_DELETE, $post)) {
            $to_delete = array_map(
                function ($id) {
                    return (int) $id;
                },
                $post[self::C_TO_DELETE]
            );
        }

        foreach ($scales as $scale) {
            $delete = in_array($scale->getId(), $to_delete);
            $ret[] = [
                "object" => $scale,
                "delete" => $delete,
                "errors" => array(),
                "message" => array()
            ];
        }
        return $ret;
    }

    protected function createProcessingArrayForDelete(array $scales, array $post = [])
    {
        if (
            !array_key_exists(self::C_TO_DELETE, $post) ||
            count($post[self::C_TO_DELETE]) == 0
        ) {
            return [];
        }
        $current = $this->createProcessingArray($scales);
        $to_delete = array_map(
            function ($del) {
                return (int) $del;
            },
            $post[self::C_TO_DELETE]
        );
        foreach ($current as &$record) {
            /**
             * @var Scale\Scale %scale
             */
            $scale = $record["object"];
            $record["delete"] = in_array($scale->getId(), $to_delete) && $scale->getId() != -1;
        }
        return array_filter(
            $current,
            function ($record) {
                return $record["delete"];
            }
        );
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }

    /**
     * @inheritDoc
     */
    protected function fillRow()
    {
        return function (ilTMSTableGUI $table, array $set) {
            /**
             * @var Scale\Scale $scale
             */
            $scale = $set["object"];
            $errors = $set["errors"];

            $tpl = $table->getTemplate();

            $tpl->setVariable("POST_VAR_DELETE", self::C_TO_DELETE);
            $tpl->setVariable("POST_VAR_HIDDEN", self::C_ID);
            $tpl->setVariable("ID", $scale->getID());

            $si = $this->getSelectInputGUIFor(self::C_SPAN_START, $scale->getSpanStart());
            if (array_key_exists(self::C_SPAN_START, $errors)) {
                $this->addErrorLine($tpl, $errors[self::C_SPAN_START]);
            }
            $tpl->setCurrentBlock("column");
            $tpl->setVariable("VALUE", $si->render());
            $tpl->parseCurrentBlock("column");

            $si = $this->getSelectInputGUIFor(self::C_SPAN_END, $scale->getSpanEnd());
            if (array_key_exists(self::C_SPAN_END, $errors)) {
                $this->addErrorLine($tpl, $errors[self::C_SPAN_END]);
            }
            $tpl->setCurrentBlock("column");
            $tpl->setVariable("VALUE", $si->render());
            $tpl->parseCurrentBlock("column");

            $si = $this->getSelectInputGUIFor(self::C_PERCENT, $scale->getPercent(), $this->txt("percent_symbol"));
            if (array_key_exists(self::C_PERCENT, $errors)) {
                $this->addErrorLine($tpl, $errors[self::C_PERCENT]);
            }
            $tpl->setCurrentBlock("column");
            $tpl->setVariable("VALUE", $si->render());
            $tpl->parseCurrentBlock("column");

            $message = $set["message"];
            if (!is_null($message) && count($message) > 0) {
                $message = array_map(function ($mes) {
                    return $this->txt($mes);
                }, $message);
                $tpl->setCurrentBlock("message");
                $tpl->setVariable("MESSAGE_CSS_ROW", "");
                $tpl->setVariable("MESSAGE", implode(",", $message));
                $tpl->parseCurrentBlock();
            }
        };
    }

    protected function addErrorLine(ilGlobalTemplateInterface $tpl, array $errors)
    {
        $errors = array_map(
            function ($err) {
                return $this->txt($err);
            },
            $errors
        );

        $tpl->setCurrentBlock("value_alert");
        $tpl->setVariable("IMG_ALERT", \ilUtil::getImagePath("icon_alert.svg"));
        $tpl->setVariable("ALT_ALERT", $this->txt("alert"));
        $tpl->setVariable("TXT_ALERT", implode(",", $errors));
        $tpl->parseCurrentBlock();
    }

    protected function getSelectInputGUIFor(string $post_name, int $value, string $append = "") : ilSelectInputGUI
    {
        $si = new ilSelectInputGUI("", $post_name . "[]");
        $si->setValue($value);
        $si->setOptions($this->getOptions($append));

        return $si;
    }

    /**
     * @return string[]
     */
    protected function getOptions(string $append = "") : array
    {
        $options = [-1 => $this->txt("please_select")];

        for ($i = 1; $i <= self::MAX_VALUE; $i++) {
            $options[$i] = $i . " " . $append;
        }

        return $options;
    }

    /**
     * @inheritDoc
     */
    protected function tableCommand()
    {
        return self::CMD_SHOW_SCALES;
    }

    /**
     * @inheritDoc
     */
    protected function tableId()
    {
        return "scales";
    }
}

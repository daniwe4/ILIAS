<?php

namespace CaT\Plugins\BookingModalities\Settings\SelectableReasons;

use CaT\Plugins\BookingModalities\ilActions;

require_once("Services/Table/classes/class.ilTable2GUI.php");
require_once(__DIR__ . "/class.ilSelectableReasonsGUI.php");

/**
 * Lists all selectable reasons
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilSelectableReasonsTableGUI extends \ilTable2GUI
{
    const CHECKED = 'checked="checked"';
    /**
     * @var \ilSelectableReasonsGUI
     */
    protected $parent_object;

    /**
     * @var ilActions
     */
    protected $actions;

    /**
     * @var ilCtr
     */
    protected $g_ctrl;

    public function __construct(\ilSelectableReasonsGUI $parent_object, ilActions $actions)
    {
        $this->setId("selectable_reasons");
        parent::__construct($parent_object);

        global $DIC;
        $this->g_ctrl = $DIC->ctrl();

        $this->actions = $actions;

        $this->configurateTable();
        $this->counter = 0;
    }

    /**
     * Create the the structure of the table
     */
    protected function configurateTable()
    {
        $this->setEnableTitle(true);
        $this->setShowRowsSelector(false);
        $this->setTopCommands(true);
        $this->setEnableHeader(true);
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(false);

        $this->setRowTemplate("tpl.selectable_reason_row.html", $this->actions->getPlugin()->getDirectory());

        $this->setTitle($this->actions->getPlugin()->txt("tbl_selectable_reasons"));
        $this->setFormAction($this->g_ctrl->getFormAction($this->parent_obj));

        $this->addColumn("", "", "1", true);
        $this->addColumn($this->actions->getPlugin()->txt("reason"), 'reason');
        $this->addColumn($this->actions->getPlugin()->txt("active"));
        $this->determineOffsetAndOrder();
    }

    /**
     * @param mixed[] 	$a_set
     */
    protected function fillRow($a_set)
    {
        $reason = $a_set["object"];
        $errors = $a_set["errors"];
        $message = $a_set["message"];

        $this->tpl->setVariable("ID", $this->counter);
        $this->tpl->setVariable("POST_VAR", \ilSelectableReasonsGUI::F_DELETE_IDS);

        $this->tpl->setVariable("COUNTER", $this->counter);
        $this->tpl->setVariable("HIDDEN_ID", $reason->getId());
        $this->tpl->setVariable("HIDDEN_ID_POST", \ilSelectableReasonsGUI::F_HIDDEN_IDS . "[" . $this->counter . "]");

        $ti = new \ilTextInputGUI("", \ilSelectableReasonsGUI::F_REASON . "[" . $this->counter . "]");
        $ti->setValue($reason->getReason());
        $this->tpl->setVariable("REASON", $ti->render());

        if (array_key_exists("reason", $errors)) {
            $caption_errors = $errors["reason"];
            $caption_errors = array_map(function ($err) {
                return $this->actions->getPlugin()->txt($err);
            }, $caption_errors);
            $this->tpl->setCurrentBlock("reason_alert");
            $this->tpl->setVariable("IMG_ALERT", \ilUtil::getImagePath("icon_alert.svg"));
            $this->tpl->setVariable("ALT_ALERT", $this->actions->getPlugin()->txt("alert"));
            $this->tpl->setVariable("TXT_ALERT", implode(",", $caption_errors));
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable("ACTIVE_POST", \ilSelectableReasonsGUI::F_ACTIVE . "[" . $this->counter . "]");
        if ($reason->getActive()) {
            $this->tpl->setVariable("CHECKED", self::CHECKED);
        }

        if (count($message) > 0) {
            $message = array_map(function ($mes) {
                return $this->actions->getPlugin()->txt($mes);
            }, $message);
            $this->tpl->setCurrentBlock("message");
            $this->tpl->setVariable("MESSAGE_CSS_ROW", $this->css_row);
            $this->tpl->setVariable("MESSAGE", implode("<br />", $message));
            $this->tpl->parseCurrentBlock();
        }

        $this->counter++;
    }
}

<?php
namespace CaT\Plugins\Accounting\Config\CostType;

use \CaT\Plugins\Accounting\ilPluginActions;

require_once("Services/Table/classes/class.ilTable2GUI.php");

/**
 * Table for CostType
 *
 * @author Daniel Weise 	<daniel.weise@concepts-and-training.de>
 */
class ilCostTypeTableGUI extends \ilTable2GUI
{
    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var int
     */
    protected $counter;

    public function __construct(
        \ilCostTypeGUI $parent_object,
        string $parent_cmd,
        \Closure $txt
    ) {
        $this->setId("costtype");
        parent::__construct($parent_object, $parent_cmd);

        $this->txt = $txt;
        $this->counter = 0;

        $this->configurateTable();
    }

    /**
     * Create the the structure of the table
     */
    protected function configurateTable()
    {
        $this->setEnableTitle(true);
        $this->setTitle($this->txt("xacc_costtype"));
        $this->setTopCommands(true);
        $this->setEnableHeader(true);
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(false);
        $this->setShowRowsSelector(false);
        $this->setLimit(0);
        $this->setDefaultOrderField("name");

        $this->addColumn("", "", "1", true);
        $this->addColumn($this->txt("xacc_ex_id"), "label");
        $this->addColumn($this->txt("xacc_label") . ' <span class="asterisk">*</span>');
        $this->addColumn($this->txt("xacc_active"), "active");

        $this->determineOffsetAndOrder();

        $this->counter = 0;
    }

    public function fillRow($a_set)
    {
        /**
         * @var CostType
         */
        $costtype = $a_set["object"];
        $errors = $a_set["errors"];
        $message = $a_set["message"];

        $this->tpl->setVariable("ID", $costtype->getId());
        $this->tpl->setVariable("POST_VAR", ilPluginActions::F_DELETE_COSTTYPE_IDS);
        $this->tpl->setVariable("COUNTER", $this->counter);

        require_once("Services/Form/classes/class.ilTextInputGUI.php");
        $ti = new \ilTextInputGUI("", ilPluginActions::F_COSTTYPE_VALUE . "[" . $this->counter . "]");
        $ti->setValue($costtype->getValue());
        $this->tpl->setVariable("VALUE", $ti->render());
        $this->tpl->setVariable("OLD_VALUE", $costtype->getValue());

        if (array_key_exists("value", $errors)) {
            $value_errors = $errors["value"];
            $value_errors = array_map(
                function ($err) {
                    return $this->txt($err);
                },
                $value_errors
            );
            $this->tpl->setCurrentBlock("value_alert");
            $this->tpl->setVariable("IMG_ALERT", \ilUtil::getImagePath("icon_alert.svg"));
            $this->tpl->setVariable("ALT_ALERT", $this->txt("alert"));
            $this->tpl->setVariable("TXT_ALERT", implode(",", $value_errors));
            $this->tpl->parseCurrentBlock();
        }

        $ti = new \ilTextInputGUI("", ilPluginActions::F_COSTTYPE_LABEL . "[" . $this->counter . "]");
        $ti->setValue($costtype->getLabel());
        $this->tpl->setVariable("LABEL", $ti->render());
        $this->tpl->setVariable("OLD_LABEL", htmlspecialchars($costtype->getLabel()));

        if (array_key_exists("label", $errors)) {
            $label_errors = $errors["label"];
            $label_errors = array_map(
                function ($err) {
                    return $this->txt($err);
                },
                $label_errors
            );
            $this->tpl->setCurrentBlock("label_alert");
            $this->tpl->setVariable("IMG_ALERT", \ilUtil::getImagePath("icon_alert.svg"));
            $this->tpl->setVariable("ALT_ALERT", $this->txt("alert"));
            $this->tpl->setVariable("TXT_ALERT", implode(",", $label_errors));
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable("ACTIVE", ilPluginActions::F_COSTTYPE_ACTIVE);
        if ($costtype->getActive()) {
            $this->tpl->touchBlock("checked");
        }

        if (count($message) > 0) {
            $message = array_map(function ($mes) {
                return $this->txt($mes);
            }, $message);
            $this->tpl->setCurrentBlock("message");
            $this->tpl->setVariable("MESSAGE_CSS_ROW", $this->css_row);
            $this->tpl->setVariable("MESSAGE", implode(",", $message));
            $this->tpl->parseCurrentBlock();
        }

        $this->counter++;
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}

<?php
namespace CaT\Plugins\Accounting\Config\VatRate;

use \CaT\Plugins\Accounting\ilPluginActions;

require_once("Services/Table/classes/class.ilTable2GUI.php");

/**
 * Table for VatRate
 *
 * @author Daniel Weise 	<daniel.weise@concepts-and-training.de>
 */
class ilVatRateTableGUI extends \ilTable2GUI
{
    /**
     * @var \Closure
     */
    protected $txt;
    /**
     * @var int
     */
    protected $counter;

    /**
     * @var \ilAccountingPlugin
     */
    protected $plugin_object;

    public function __construct(
        \ilVatRateGUI $parent_object,
        string $parent_cmd,
        \Closure $txt
    ) {
        parent::__construct($parent_object, $parent_cmd);
        $this->counter = 0;
        $this->txt = $txt;
        $this->configurateTable();
    }

    /**
     * Create the the structure of the table
     */
    protected function configurateTable()
    {
        $this->setEnableTitle(true);
        $this->setTitle($this->txt("xacc_vatrate"));
        $this->setTopCommands(true);
        $this->setEnableHeader(true);
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(false);
        $this->setShowRowsSelector(false);
        $this->setLimit(0);
        $this->setDefaultOrderField("label");
        $this->setDefaultOrderDirection("asc");

        $this->addColumn("", "", "1", true);
        $this->addColumn($this->txt("xacc_value") . ' <span class="asterisk">*</span>', "value");
        $this->addColumn($this->txt("xacc_label") . ' <span class="asterisk">*</span>', "label");
        $this->addColumn($this->txt("xacc_active"), "active");

        $this->determineOffsetAndOrder();

        $this->counter = 0;
    }

    /**
     * Fill the rows of the table
     *
     * @param \VatRate  $a_set      holds an VatRate object
     */
    public function fillRow($a_set)
    {
        $vatrate = $a_set["object"];
        $errors = $a_set["errors"];
        $message = $a_set["message"];

        $this->tpl->setVariable("ID", $vatrate->getId());
        $this->tpl->setVariable("POST_VAR", ilPluginActions::F_DELETE_VATRATE_IDS);
        $this->tpl->setVariable("COUNTER", $this->counter);

        require_once("Services/Form/classes/class.ilTextInputGUI.php");
        $ti = new \ilTextInputGUI("", ilPluginActions::F_VATRATE_VALUE . "[" . $this->counter . "]");
        $value = $vatrate->getValue();
        if ($value != "") {
            $value = number_format((float) $value, 2, ',', '');
        }
        $ti->setValue($value);
        $this->tpl->setVariable("VALUE", $ti->render());

        $old_value = $vatrate->getValue();
        if (array_key_exists("value", $errors)) {
            $value_errors = $errors["value"];
            $value_errors = array_map(function ($err) {
                return $this->txt($err);
            }, $value_errors);
            $this->tpl->setCurrentBlock("value_alert");
            $this->tpl->setVariable("IMG_ALERT", \ilUtil::getImagePath("icon_alert.svg"));
            $this->tpl->setVariable("ALT_ALERT", $this->txt("alert"));
            $this->tpl->setVariable("TXT_ALERT", implode(",", $value_errors));
            $this->tpl->parseCurrentBlock();

            $old_value = $a_set["old_value"];
        }
        $this->tpl->setVariable("OLD_VALUE", $old_value);

        $ti = new \ilTextInputGUI("", ilPluginActions::F_VATRATE_LABEL . "[" . $this->counter . "]");
        $ti->setValue($vatrate->getLabel());
        $this->tpl->setVariable("LABEL", $ti->render());

        $old_label = htmlspecialchars($vatrate->getLabel());
        if (array_key_exists("label", $errors)) {
            $label_errors = $errors["label"];
            $label_errors = array_map(function ($err) {
                return $this->txt($err);
            }, $label_errors);
            $this->tpl->setCurrentBlock("label_alert");
            $this->tpl->setVariable("IMG_ALERT", \ilUtil::getImagePath("icon_alert.svg"));
            $this->tpl->setVariable("ALT_ALERT", $this->txt("alert"));
            $this->tpl->setVariable("TXT_ALERT", implode(",", $label_errors));
            $this->tpl->parseCurrentBlock();

            $old_label = htmlspecialchars($a_set["old_label"]);
        }
        $this->tpl->setVariable("OLD_LABEL", $old_label);

        $this->tpl->setVariable("ACTIVE", ilPluginActions::F_VATRATE_ACTIVE);
        if ($vatrate->getActive()) {
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

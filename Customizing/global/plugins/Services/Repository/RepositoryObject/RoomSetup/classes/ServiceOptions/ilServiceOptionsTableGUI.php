<?php

declare(strict_types=1);

namespace CaT\Plugins\RoomSetup\ServiceOptions;

use \CaT\Plugins\RoomSetup\ilPluginActions;

require_once("Services/Table/classes/class.ilTable2GUI.php");

/**
 * Table structure view of all service options
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilServiceOptionsTableGUI extends \ilTable2GUI
{
    /**
     * @var \Closure
     */
    protected $txt;

    public function __construct(\ilServiceOptionsGUI $parent_object, \Closure $txt, string $parent_cmd)
    {
        $this->txt = $txt;
        $this->setId("servive_option");
        parent::__construct($parent_object, $parent_cmd);

        $this->setEnableTitle(true);
        $this->setTitle($this->txt("service_options"));
        $this->setTopCommands(true);
        $this->setEnableHeader(true);
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        $this->setShowRowsSelector(true);
        $this->setDefaultOrderField("name");
        $this->setDefaultOrderDirection("asc");

        $this->addColumn("", "", "1", true);
        $this->addColumn($this->txt("name"), "name");
        $this->addColumn($this->txt("is_active"), "is_active");

        $this->counter = 0;
    }

    /**
     * @inheritdoc
     */
    public function fillRow($a_set)
    {
        $service_option = $a_set["object"];
        $errors = $a_set["errors"];
        $message = $a_set["message"];

        $this->tpl->setVariable("ID", $service_option->getId());
        $this->tpl->setVariable("POST_VAR", ilPluginActions::F_DELETE_SERVICE_OPTION_IDS);
        $this->tpl->setVariable("COUNTER", $this->counter);

        require_once("Services/Form/classes/class.ilTextInputGUI.php");
        $ti = new \ilTextInputGUI("", ilPluginActions::F_SERVICE_OPTION_NAME . "[" . $this->counter . "]");
        $ti->setValue($service_option->getName());
        $this->tpl->setVariable("NAME", $ti->render());
        $this->tpl->setVariable("OLD_TITLE", $service_option->getName());

        if (array_key_exists("name", $errors)) {
            $name_errors = $errors["name"];
            $name_errors = array_map(function ($err) {
                return $this->txt($err);
            }, $name_errors);
            $this->tpl->setCurrentBlock("name_alert");
            $this->tpl->setVariable("IMG_ALERT", \ilUtil::getImagePath("icon_alert.svg"));
            $this->tpl->setVariable("ALT_ALERT", $this->txt("alert"));
            $this->tpl->setVariable("TXT_ALERT", implode(",", $name_errors));
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable("ACTIVE", ilPluginActions::F_SERVICE_OPTION_ACTIVE);
        if ($service_option->getActive()) {
            $this->tpl->touchBlock("checked");
        }

        if (
            is_array($message) &&
            count($message) > 0
        ) {
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
        assert('is_string($code)');
        $txt = $this->txt;
        return $txt($code);
    }
}

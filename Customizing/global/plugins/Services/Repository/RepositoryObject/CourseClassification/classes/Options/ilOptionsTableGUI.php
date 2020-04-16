<?php

namespace CaT\Plugins\CourseClassification\Options;

require_once("Services/Table/classes/class.ilTable2GUI.php");

class ilOptionsTableGUI extends \ilTable2GUI
{
    const F_DELETE_IDS = "to_delete_ids";
    const F_CAPTION = "caption";
    /**
     * @var \ilOptionsGUI
     */
    protected $parent_object;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var ilActions
     */
    protected $actions;

    /**
     * @var ilCtr
     */
    protected $g_ctrl;

    /**
     * @param string 	$type
     */
    public function __construct(\ilOptionsGUI $parent_object, ilActions $actions, $type)
    {
        $this->setId(strtolower($this->type));
        parent::__construct($parent_object);

        global $DIC;
        $this->g_ctrl = $DIC->ctrl();

        $this->type = $type;
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

        $this->setRowTemplate("tpl.option_row.html", $this->actions->getPlugin()->getDirectory());

        $this->setTitle($this->actions->getPlugin()->txt("tbl_" . strtolower($this->type)));
        $this->setFormAction($this->g_ctrl->getFormAction($this->parent_obj));

        $this->addColumn("", "", "1", true);
        $this->addColumn($this->actions->getPlugin()->txt("caption"));
    }

    /**
     * @param Option 	$a_set
     */
    protected function fillRow($a_set)
    {
        $option = $a_set["option"];
        $errors = $a_set["errors"];
        $message = $a_set["message"];

        $this->tpl->setVariable("ID", $this->counter);
        $this->tpl->setVariable("POST_VAR", self::F_DELETE_IDS);

        $this->tpl->setVariable("COUNTER", $this->counter);
        $this->tpl->setVariable("HIDDEN_ID", $option->getId());

        require_once("Services/Form/classes/class.ilTextInputGUI.php");
        $ti = new \ilTextInputGUI("", self::F_CAPTION . "[" . $this->counter . "]");
        $ti->setValue($option->getCaption());
        $this->tpl->setVariable("CAPTION", $ti->render());

        if (array_key_exists("caption", $errors)) {
            $caption_errors = $errors["caption"];
            $caption_errors = array_map(function ($err) {
                return $this->actions->getPlugin()->txt($err);
            }, $caption_errors);
            $this->tpl->setCurrentBlock("caption_alert");
            $this->tpl->setVariable("IMG_ALERT", \ilUtil::getImagePath("icon_alert.svg"));
            $this->tpl->setVariable("ALT_ALERT", $this->actions->getPlugin()->txt("alert"));
            $this->tpl->setVariable("TXT_ALERT", implode(",", $caption_errors));
            $this->tpl->parseCurrentBlock();
        }

        if (!is_null($message) && count($message) > 0) {
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

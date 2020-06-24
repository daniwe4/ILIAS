<?php

namespace CaT\Plugins\MaterialList\Lists;

use \CaT\Plugins\MaterialList;

require_once("Services/Table/classes/class.ilTable2GUI.php");

class ilListsTableGUI extends \ilTable2GUI
{
    /**
     * @param bool 	$write_access
     */
    public function __construct(\ilListsGUI $parent_object, MaterialList\ilPluginActions $plugin_actions, \Closure $txt, $write_access)
    {
        parent::__construct($parent_object);

        global $DIC;

        $this->g_ctrl = $DIC->ctrl();

        $this->txt = $txt;
        $this->mode = $plugin_actions->getBehavior();
        $this->write_access = $write_access;

        $this->setEnableTitle(true);
        $this->setTitle($this->txt("lists_table_title"));
        $this->setTopCommands(true);
        $this->setEnableHeader(true);
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        $this->setRowTemplate("tpl.list_entry_row.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/MaterialList");
        $this->setShowRowsSelector(false);

        $width = "25%";
        if ($this->write_access) {
            $this->addColumn("", "", "1", true);
        }

        $this->addColumn($this->txt("lists_article_number_and_title"), false, $width);
        $this->addColumn($this->txt("lists_number_per_participant"), false, $width);
        $this->addColumn($this->txt("lists_number_per_course"), false, $width);

        $this->counter = 0;
    }

    public function fillRow($a_set)
    {
        require_once("Services/Form/classes/class.ilNumberInputGUI.php");
        require_once("Services/Form/classes/class.ilTextInputGUI.php");

        if ($this->write_access) {
            $this->tpl->setVariable("POST_VAR", MaterialList\ilObjectActions::F_LIST_ENTRY_TO_DELETE_IDS);
            $this->tpl->setVariable("ID", $a_set->getId());
        }

        $fault_entry = $this->getFaultEntry($this->counter);

        $ni = $this->getMultiTextInputGUI(MaterialList\ilObjectActions::F_LIST_ENTRY_NUMPER_PER_PARTICIPANT, $a_set->getNumberPerParticipant(), $fault_entry);
        $this->tpl->setVariable("NUMBER_PER_PARTICIPANT", $ni->render());

        $ni = $this->getMultiTextInputGUI(MaterialList\ilObjectActions::F_LIST_ENTRY_NUMBER_PER_COURSE, $a_set->getNumberPerCourse(), $fault_entry);
        $this->tpl->setVariable("NUMBER_PER_COURSE", $ni->render());

        $delimeter = "-";
        if ($a_set->getArticleNumber() == "") {
            $delimeter = "";
        }
        $ti = $this->getTextInputGUI(MaterialList\ilObjectActions::F_LIST_ENTRY_TITLE, $a_set->getArticleNumber() . $delimeter . $a_set->getTitle(), $fault_entry);
        $ti->setMaxLength(300);
        $this->tpl->setVariable("TITLE", $ti->render());

        $this->tpl->setVariable("HIDDEN_ID_POST", MaterialList\ilObjectActions::F_LIST_ENTRY_HIDDEN_IDS . "[]");
        $this->tpl->setVariable("HIDDEN_ID", $a_set->getId());

        $this->counter++;
    }

    /**
     * Get a number input gui
     *
     * @param string 	$post_var
     * @param int 		$value
     * @param array 	$fault_entry
     *
     * @return \ilNumberInputGUI
     */
    protected function getMultiTextInputGUI($post_var, $value, $fault_entry)
    {
        $ni = new \ilTextInputGUI("", $post_var . "[]");
        $ni->setDisabled(!$this->write_access);

        if ($fault_entry && array_key_exists($post_var, $fault_entry)) {
            $this->tpl->setCurrentBlock("alert_" . $post_var);
            $this->tpl->setVariable("IMG_ALERT", \ilUtil::getImagePath("icon_alert.svg"));
            $this->tpl->setVariable("ALT_ALERT", $this->txt("alert"));

            foreach ($fault_entry[$post_var] as $failed) {
                $f_string[] = $this->txt($failed);
            }

            $this->tpl->setVariable("TXT_ALERT", implode("<br />", $f_string));
            $this->tpl->parseCurrentBlock();
        }

        if ($value == 0) {
            $value = "";
        }

        $ni->setValue($value);

        return $ni;
    }

    /**
     * Get a text input gui
     *
     * @param string 	$post_var
     * @param int 		$value
     * @param array 	$fault_entry
     *
     * @return \ilTextInputGUI
     */
    protected function getTextInputGUI($post_var, $value, $fault_entry)
    {
        $ti = new \ilTextInputGUI("", $post_var . $this->counter);
        $ti->setDisabled(!$this->write_access);

        if ($this->mode != MaterialList\ilPluginActions::MATERIAL_MODE_FREE) {
            $ti->setDataSource($this->g_ctrl->getLinkTarget($this->parent_obj, 'getArticleNumberAndTitle', "", true));
            $ti->setSubmitFormOnEnter(false);
        }

        if ($fault_entry && array_key_exists($post_var, $fault_entry)) {
            $this->tpl->setCurrentBlock("alert_" . $post_var);
            $this->tpl->setVariable("IMG_ALERT", \ilUtil::getImagePath("icon_alert.svg"));
            $this->tpl->setVariable("ALT_ALERT", $this->txt("alert"));

            foreach ($fault_entry[$post_var] as $failed) {
                $f_string[] = $this->txt($failed);
            }

            $this->tpl->setVariable("TXT_ALERT", implode("<br />", $f_string));
            $this->tpl->parseCurrentBlock();
        }

        if (trim($value) == "-") {
            $ti->setValue("");
        } else {
            $ti->setValue($value);
        }

        return $ti;
    }

    /**
     * Set check results
     *
     * @param [string[]]
     *
     * @return null
     */
    public function setFaults(array $faults)
    {
        $this->faults = $faults;
    }

    /**
     * Get single check result
     *
     * @param int 	$id
     *
     * @return string[]
     */
    protected function getFaultEntry($id)
    {
        return $this->faults[$id];
    }

    /**
     * Translate code to lang value
     *
     * @param string 	$code
     *
     * @return string
     */
    protected function txt(string $code)
    {
        $txt = $this->txt;

        return $txt($code);
    }
}

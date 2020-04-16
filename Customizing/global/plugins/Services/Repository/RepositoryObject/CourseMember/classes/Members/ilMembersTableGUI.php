<?php

namespace CaT\Plugins\CourseMember\Members;

use CaT\Plugins\CourseMember;

require_once("Services/Table/classes/class.ilTable2GUI.php");
require_once(__DIR__ . "/class.ilMembersGUI.php");

/**
 * Lists member objects as table
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-raining.de>
 */
class ilMembersTableGUI extends \ilTable2GUI
{
    /**
     * @param string 	$type
     */
    public function __construct(
        \ilMembersGUI $parent_object,
        CourseMember\ilObjActions $object_actions,
        CourseMember\LPOptions\ilActions $lp_options_actions,
        $should_edit,
        $max_credits = null,
        $max_idd_learning_time = null
    ) {
        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->setId('table_members');
        parent::__construct($parent_object, $parent_object::CMD_SHOW_MEMBERS);

        $this->object_actions = $object_actions;
        $this->lp_options_actions = $lp_options_actions;
        $this->max_credits = $max_credits;
        $this->max_idd_learning_time = $max_idd_learning_time;
        $this->standard_id = null;

        $this->setEnableTitle(true);
        $this->setShowRowsSelector(false);
        $this->setTopCommands(true);
        $this->setEnableHeader(true);
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(false);
        $this->setShowRowsSelector(true);
        $this->setDefaultOrderField("lastname");
        $this->setDefaultOrderDirection("asc");
        $this->setFormAction($this->g_ctrl->getFormAction($parent_object));

        $this->setRowTemplate("tpl.member_row.html", $this->object_actions->getObject()->getDirectory());

        $this->addColumn($this->txt("lastname"), "lastname");
        $this->addColumn($this->txt("firstname"), "firstname");
        $this->addColumn($this->txt("login"));
        $this->addColumn($this->txt("lp_value"));
        // Need this for tms 488
        // if($this->max_credits) {
        // 	$this->addColumn($this->txt("credits"));
        // }

        if ($this->max_idd_learning_time) {
            $this->addColumn($this->txt("idd_learning_time"));
        }

        $this->addColumn($this->txt("last_edited"));
        $this->addColumn($this->txt("last_edit_by"));

        $this->closed = $this->object_actions->getObject()->getSettings()->getClosed();
        $this->options = $this->lp_options_actions->getSelectInputOptions();
        $this->should_edit = $should_edit;

        $this->counter = 0;
    }

    /**
     * @inheritdoc
     */
    public function fillRow($a_set)
    {
        $object = $a_set["object"];
        $errors = $a_set["errors"];
        require_once("Services/User/classes/class.ilObjUser.php");
        $this->tpl->setVariable("FIRSTNAME", $object->getLastname());
        $this->tpl->setVariable("LASTNAME", $object->getFirstname());
        $this->tpl->setVariable("LOGIN", $object->getLogin());

        $this->tpl->setVariable("USER_ID_POST", \ilMembersGUI::F_USER_ID . "[]");
        $this->tpl->setVariable("CRS_ID_POST", \ilMembersGUI::F_CRS_ID . "[]");
        $this->tpl->setVariable("LP_ID_POST", \ilMembersGUI::F_LP_ID . "[]");
        $this->tpl->setVariable("LP_VALUE_POST", \ilMembersGUI::F_LP_VALUE . "[]");
        $this->tpl->setVariable("ILIAS_LP_POST", \ilMembersGUI::F_ILIAS_LP . "[]");

        $this->tpl->setVariable("USER_ID", $object->getUserId());
        $this->tpl->setVariable("CRS_ID", $object->getCrsId());
        $this->tpl->setVariable("LP_ID", $object->getLPId());
        $this->tpl->setVariable("LP_VALUE", $object->getLPValue());
        $this->tpl->setVariable("ILIAS_LP", $object->getILIASLP());

        require_once("Services/Form/classes/class.ilSelectInputGUI.php");
        $si = new \ilSelectInputGUI("", \ilMembersGUI::F_NEW_LP_ID . "[]");
        $options = array(null => $this->txt("please_select"));
        $sel_options = $this->getOptionsArray();

        if (!array_key_exists($object->getLPId(), $sel_options)) {
            $sel_options[$object->getLPId()] = $object->getLPValue();
        }
        $options = $options + $sel_options;
        $si->setOptions($options);

        $val = $this->getStandardId();
        if (!is_null($object->getLPId())) {
            $val = $object->getLPId();
        }
        $si->setValue($val);
        $si->setDisabled($this->closed || !$this->should_edit);
        $this->tpl->setVariable("NEW_LP_ID", $si->render());

        // Need this for tms 488
        // if($this->max_credits) {
        // 	$this->addInputForCredits($object, $errors);
        // }

        if ($this->max_idd_learning_time) {
            $this->addInputForIDDLearningTime($object, $errors);
        }

        $last_edited = "-";
        if ($object->getLastEdited()) {
            $last_edited = $object->getLastEdited()->get(IL_CAL_DATETIME);
        }

        $this->tpl->setVariable("LAST_EDITED", $last_edited);

        $last_edit_by = "-";
        if ($object->getLastEditBy()) {
            $last_edited = \ilObjUser::_lookupLogin($object->getLastEditBy());
        }
        $this->tpl->setVariable("LAST_EDIT_BY", $last_edited);
    }

    protected function getStandardId()
    {
        if (is_null($this->standard_id)) {
            $standard = array_shift(
                array_filter($this->options, function ($a) {
                    if ($a->isStandard()) {
                        return $a;
                    }
                })
            );

            if (!is_null($standard)) {
                $this->standard_id = $standard->getId();
            }
        }

        return $this->standard_id;
    }

    protected function getOptionsArray()
    {
        if (is_null($this->sel_options)) {
            $this->sel_options = array();
            foreach ($this->options as $option) {
                $this->sel_options[$option->getId()] = $option->getTitle();
            }
        }

        return $this->sel_options;
    }

    /**
     * Fills the column for credits
     *
     * @param Member 	$object
     * @param string[] 	$errors
     *
     * @return void
     */
    protected function addInputForCredits($object, array $errors)
    {
        require_once("Services/Form/classes/class.ilNumberInputGUI.php");
        $si = new \ilNumberInputGUI("", \ilMembersGUI::F_CREDITS . "[]");
        $si->allowDecimals(true);
        $si->setMaxValue($this->object_actions->getObject()->getSettings()->getCredits(), false);
        $credits = $object->getCredits();
        if (is_null($credits)) {
            $credits = $this->object_actions->getObject()->getSettings()->getCredits();
        }

        $si->setValue($credits);
        $si->setDisabled($this->closed || !$this->should_edit);
        $this->tpl->setVariable("CREDITS", $si->render());
        if (array_key_exists("credit", $errors)) {
            $caption_errors = $errors["credit"];

            $caption_errors = array_map(function ($err) {
                return sprintf($this->txt($err), $this->object_actions->getObject()->getSettings()->getCredits());
            }, $caption_errors);

            $this->tpl->setCurrentBlock("credit_alert");
            $this->tpl->setVariable("IMG_ALERT_CREDIT", \ilUtil::getImagePath("icon_alert.svg"));
            $this->tpl->setVariable("ALT_ALERT_CREDIT", $this->txt("alert"));
            $this->tpl->setVariable("TXT_ALERT_CREDIT", implode(",", $caption_errors));
            $this->tpl->parseCurrentBlock();
        }
    }

    /**
     * Fills the column for idd learning time
     *
     * @param Member 	$object
     * @param string[] 	$errors
     *
     * @return void
     */
    protected function addInputForIDDLearningTime($object, array $errors)
    {
        require_once("Services/Form/classes/class.ilTimeInputGUI.php");
        $si = new \ilTimeInputGUI("", \ilMembersGUI::F_IDD_LEARNING_TIME . "[" . $this->counter . "]");
        $si->setMaxHours(250);
        $idd_learning_time = $object->getIDDLearningTime();

        if (is_null($idd_learning_time)) {
            $idd_learning_time = $this->max_idd_learning_time;
        }

        $value = $this->transformIDDLearningTimeToString($idd_learning_time);
        $si->setValue($value);
        $si->setDisabled($this->closed || !$this->should_edit);
        if (array_key_exists("learning_time", $errors)) {
            $caption_errors = $errors["learning_time"];

            $caption_errors = array_map(function ($err) {
                return sprintf($this->txt($err), $this->transformIDDLearningTimeToString($this->max_idd_learning_time));
            }, $caption_errors);

            $this->tpl->setCurrentBlock("idd_learning_time_alert");
            $this->tpl->setVariable("IMG_ALERT_IDD", \ilUtil::getImagePath("icon_alert.svg"));
            $this->tpl->setVariable("ALT_ALERT_IDD", $this->txt("alert"));
            $this->tpl->setVariable("TXT_ALERT_IDD", implode(",", $caption_errors));
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setCurrentBlock("idd_learning_time");
        $this->tpl->setVariable("IDD_LEARNING_TIME", $si->render());
        $this->tpl->parseCurrentBlock();

        $this->counter++;
    }

    /**
     * Transforms the idd minutes into printable string
     *
     * @param int 	$minutes
     *
     * @return string
     */
    protected function transformIDDLearningTimeToString($minutes)
    {
        $hours = floor($minutes / 60);
        $minutes = $minutes - $hours * 60;
        return str_pad($hours, "2", "0", STR_PAD_LEFT) . ":" . str_pad($minutes, "2", "0", STR_PAD_LEFT);
    }

    /**
     * Get options for credit select
     *
     * @return string[]
     */
    protected function getCreditOptions()
    {
        $max_credits = $this->object_actions->getObject()->getSettings()->getCredits();

        $ret = array();
        for ($i = 0; $i <= $max_credits; $i++) {
            $ret[$i] = (string) $i;
        }

        return $ret;
    }

    /**
     * Tranlsate lang code
     *
     * @param string 	$code
     *
     * @return string
     */
    protected function txt($code)
    {
        return $this->object_actions->getObject()->pluginTxt($code);
    }
}

<?php

namespace CaT\Plugins\OnlineSeminar\VC\Generic;

require_once("Services/Table/classes/class.ilTable2GUI.php");
require_once(__DIR__ . "/../../Participant/class.ilParticipantGUI.php");

/**
 * Lists all participants of the Generic vc.
 * Booked and unknown
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilGenericTableGUI extends \ilTable2GUI
{

    /**
     * @var \ilCtrl
     */
    protected $g_ctrl;

    /**
     * @param string  	$parent_cmd
     * @param string 	$template_context
     */
    public function __construct(
        \ilGenericGUI $parent,
        ilActions $vc_actions,
        \CaT\Plugins\OnlineSeminar\ilActions $actions,
        $parent_cmd = "",
        $template_context = ""
    ) {
        $this->setId("generic_member");
        parent::__construct($parent, $parent_cmd, $template_context);

        $this->vc_actions = $vc_actions;
        $this->actions = $actions;

        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->g_user = $DIC->user();
        $this->g_access = $DIC->access();
        $DIC->language()->loadLanguageModule("trac");

        $this->finished = $this->vc_actions->getObject()->getSettings()->isFinished();
        $this->setEnableTitle(true);
        $this->setShowRowsSelector(false);
        $this->setTopCommands(true);
        $this->setEnableHeader(true);
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        if (!$this->finished) {
            $this->setSelectAllCheckbox(\ilParticipantGUI::F_CANCEL_ID);
        }
        $this->setTitle($this->txt("tbl_generic"));
        $this->setRowTemplate("tpl.generic_row.html", $this->vc_actions->getObject()->getDirectory());
        $this->setFormAction($this->g_ctrl->getFormAction($this->parent_obj));

        if ($this->g_access->checkAccessOfUser(
            $this->g_user->getId(),
            "edit_member",
            "",
            $this->vc_actions->getObject()->getRefId()
            )
            && !$this->finished
        ) {
            $this->addMultiCommand(\ilParticipantGUI::CMD_CANCEL_PARTICIPANTS_CONFIRM, $this->txt("delete"));
        }

        if ($this->g_access->checkAccessOfUser(
            $this->g_user->getId(),
            "edit_participation",
            "",
            $this->vc_actions->getObject()->getRefId()
            )
            && !$this->finished
        ) {
            $this->addMultiCommand(\ilParticipantGUI::CMD_MARK_PARTICIPATED, $this->txt("participated"));
            $this->addMultiCommand(\ilParticipantGUI::CMD_UNMARK_PARTICIPATED, $this->txt("not_participated"));
        }

        $this->addColumn("", "", "1", true);
        $this->addColumn($this->txt("name"), "sortByName");
        $this->addColumn($this->txt("user_name"), "sortByLogin");
        $this->addColumn($this->txt("email"), "sortByMail");
        $this->addColumn($this->txt("phone"), "sortByPhone");
        $this->addColumn($this->txt("minutes"), "sortByMinutes");
        $this->addColumn($this->txt("status"), "sortByStatus");
        $this->addColumn($this->txt("actions"), null);

        $this->counter = 0;
    }

    protected function fillRow($a_set)
    {
        $known_user = $a_set->isKnownUser();

        if (!$this->finished) {
            $this->tpl->setVariable("ID", $a_set->getId());
            $this->tpl->setVariable("POST_VAR", \ilParticipantGUI::F_CANCEL_ID);
            if ($known_user) {
                $this->tpl->setVariable("HIDDEN_ID", $a_set->getUserId());
            }
            $this->tpl->setVariable("COUNTER", $this->counter);
        }

        if ($known_user) {
            require_once("Services/Tracking/classes/class.ilLearningProgressBaseGUI.php");
            $status = $this->getLPStatusOf($a_set->getUserId());
            $path = \ilLearningProgressBaseGUI::_getImagePathForStatus($status);
            $text = \ilLearningProgressBaseGUI::_getStatusText($status);
            $this->tpl->setVariable("STATUS", \ilUtil::img($path, $text));
        }

        $this->tpl->setVariable("NAME", $a_set->getName());
        $this->tpl->setVariable("USER_NAME", $a_set->getUserName());
        $this->tpl->setVariable("EMAIL", $a_set->getEmail());
        $this->tpl->setVariable("PHONE", $a_set->getPhone());

        $minutes = $a_set->getMinutes();
        if ($minutes == null) {
            $minutes = "";
        }
        $this->tpl->setVariable("MINUTES", $minutes);
        if (!$this->finished) {
            $this->tpl->setVariable("ACTIONS", $this->buildActionDropDown($a_set->getId(), $a_set->getUserId(), $a_set->isKnownUser()));
        }

        $this->counter++;
    }

    /**
     * Get the action menu for each row
     *
     * @param int 	$id
     * @param int 	$user_id
     * @param bool 	$known_user
     *
     * @return string
     */
    protected function buildActionDropDown($id, $user_id, $known_user)
    {
        /**
         * TODO:
         * Switch zwischen unbekannten und bekannten Benutzern
         * Wobei bekannt eigentlich nur gebuchte sein sollten
         */
        require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';
        $l = new \ilAdvancedSelectionListGUI();
        $l->setListTitle($this->lng->txt("actions"));

        $this->g_ctrl->setParameterByClass('ilParticipantGUI', \ilParticipantGUI::GET_USER_ID, $user_id);

        if ($known_user) {
            $this->g_ctrl->setParameterByClass('ilParticipantGUI', \ilParticipantGUI::GET_USER_TYPE, "booked");
        } else {
            $this->g_ctrl->setParameterByClass('ilParticipantGUI', \ilParticipantGUI::GET_USER_TYPE, "unknown");
            $this->g_ctrl->setParameterByClass('ilParticipantGUI', \ilParticipantGUI::GET_ID, $id);
        }
        $target = $this->g_ctrl->getLinkTargetByClass('ilParticipantGUI', \ilParticipantGUI::CMD_CANCEL_PARTICIPANT_CONFIRM);

        if ($this->g_access->checkAccessOfUser(
            $this->g_user->getId(),
            "edit_member",
            "",
            $this->vc_actions->getObject()->getRefId()
        )
        ) {
            $l->addItem($this->txt('delete_user'), \ilParticipantGUI::CMD_CANCEL_PARTICIPANT_CONFIRM, $target);
        }

        $this->g_ctrl->setParameterByClass('ilParticipantGUI', \ilParticipantGUI::GET_USER_ID, null);
        $this->g_ctrl->setParameterByClass('ilParticipantGUI', \ilParticipantGUI::GET_USER_TYPE, null);
        $this->g_ctrl->setParameterByClass('ilParticipantGUI', \ilParticipantGUI::GET_ID, null);

        return $l->getHTML();
    }

    /**
     * Translate code
     *
     * @param string 	$code
     *
     * @return string
     */
    protected function txt($code)
    {
        return $this->vc_actions->getObject()->pluginTxt($code);
    }

    /**
     * Set current filter values to keep if sorting
     *
     * @param mixed[] 	$filter_values
     *
     * @return null
     */
    public function setFilterValues(array $filter_values)
    {
        $this->filter_values = $filter_values;
    }

    public function render()
    {
        $this->g_ctrl->setParameter($this->parent_obj, "filter_values", base64_encode(serialize($this->filter_values)));
        $ret = parent::render();
        $this->g_ctrl->setParameter($this->parent_obj, "filter_values", null);

        return $ret;
    }

    /**
     * Get the LP Status of user
     *
     * @param int 	$user_id
     *
     * @return int
     */
    protected function getLPStatusOf($user_id)
    {
        $lp_data = $this->actions->getLPDataFor($user_id);
        $passed = $lp_data["passed"];

        if (!is_null($passed) && (int) $passed === 1) {
            return \ilLPStatus::LP_STATUS_COMPLETED_NUM;
        }

        if (!is_null($passed) && (int) $passed === 0) {
            return \ilLPStatus::LP_STATUS_FAILED_NUM;
        }

        return \ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
    }
}

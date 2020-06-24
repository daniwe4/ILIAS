<?php

namespace CaT\Plugins\TalentAssessment\Observer;

require_once("Services/Table/classes/class.ilTable2GUI.php");

class ilObserverTableGUI extends \ilTable2GUI
{

	const CAPTION_DELETE = "delete";

	public function __construct($a_parent_obj, $a_parent_cmd = "", $a_template_context = "")
	{
		global $DIC;

		$this->g_ctrl = $DIC->ctrl();
		$this->txt = $a_parent_obj->getTXTClosure();

		$this->setId("talent_assessment_observer");
		$this->possible_cmd = $a_parent_obj->getPossibleCMD();

		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		$this->setEnableHeader(true);
		$this->setFormAction($this->g_ctrl->getFormAction($this->parent_obj));
		$this->setRowTemplate("tpl.talent_assessment_observer_list_row.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/TalentAssessment");
		$this->setEnableTitle(true);
		$this->setShowRowsSelector(false);
		$this->setExternalSorting(true);

		$this->addColumn("", "", "1", true);
		$this->addColumn($this->txt("fullname"), null);
		$this->addColumn($this->txt("login"), null);
		$this->addColumn($this->txt("email"), null);
		$this->addColumn($this->txt("action"), null);

		foreach ($this->getMultiCommands() as $cmd => $caption) {
			$this->addMultiCommand($cmd, $this->txt($caption));
		}

		$this->setSelectAllCheckbox("id[]");
		$this->setEnableAllCommand(true);
		$this->setTitle($this->txt("observer_table_title"));
		$assigned_users = $this->parent_obj->getActions()->getAssignedUsers($this->parent_obj->getObjId());
		$this->setData($assigned_users);
	}

	public function fillRow($row)
	{
		$this->tpl->setVariable("OBJ_ID", $row->getId());
		$this->tpl->setVariable("FULLNAME", $row->getLastname().", ".$row->getFirstname());
		$this->tpl->setVariable("LOGIN", $row->getLogin());
		$this->tpl->setVariable("EMAIL", $row->getEmail());
		$this->tpl->setVariable("ACTIONS", $this->getActionMenu($row->getId()));
	}

	/**
	 * @param 	string	$code
	 * @return	string
	 */
	public function txt(string $code)
	{
		$txt = $this->txt;

		return $txt($code);
	}

	/**
	 * return multicommands for requirement table gui
	 *
	 * @return array string => string
	 */
	protected function getMultiCommands()
	{
		return array($this->possible_cmd["CMD_DELETE_SELECTED_CONFIRM"] => self::CAPTION_DELETE);
	}

	protected function getActionMenu($usr_id)
	{
		include_once("Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
		$current_selection_list = new \ilAdvancedSelectionListGUI();
		$current_selection_list->setAsynch(false);
		$current_selection_list->setAsynchUrl(true);
		$current_selection_list->setListTitle($this->txt("actions"));
		$current_selection_list->setId($usr_id);
		$current_selection_list->setSelectionHeaderClass("small");
		$current_selection_list->setItemLinkClass("xsmall");
		$current_selection_list->setLinksMode("il_ContainerItemCommand2");
		$current_selection_list->setHeaderIcon(\ilAdvancedSelectionListGUI::DOWN_ARROW_DARK);
		$current_selection_list->setUseImages(false);
		$current_selection_list->setAdditionalToggleElement("usr_id".$usr_id, "ilContainerListItemOuterHighlight");

		foreach ($this->getActionMenuItems($usr_id) as $key => $value) {
			$current_selection_list->addItem($value["title"], "", $value["link"], $value["image"], "", $value["frame"]);
		}

		return $current_selection_list->getHTML();
	}

	protected function getActionMenuItems($usr_id)
	{
		$this->g_ctrl->setParameter($this->parent_obj, "usr_id", $usr_id);
		$link_delete = $this->memberlist_link = $this->g_ctrl->getLinkTarget($this->parent_obj, $this->possible_cmd["CMD_DELETE_CONFIRM"]);
		$this->g_ctrl->clearParameters($this->parent_obj);

		$items = array();
		$items[] = array("title" => $this->txt("delete_observer"), "link" => $link_delete, "image" => "", "frame"=>"");

		return $items;
	}
}

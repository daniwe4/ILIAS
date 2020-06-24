<?php

declare(strict_types=1);

namespace CaT\Plugins\TalentAssessmentReport\Report;

use \CaT\Plugins\TalentAssessmentReport\ilActions;

require_once("Services/Table/classes/class.ilTable2GUI.php");
require_once(__DIR__."/class.ilReportGUI.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/TalentAssessment/classes/class.ilTalentAssessmentObservationsGUI.php");

class ilReportUserTableGUI extends \ilTable2GUI
{
	public function __construct($a_parent_obj, \Closure $txt, ilActions $actions, $apply_filter = false, $rest_filter = false, $a_parent_cmd = "", $a_template_context = "")
	{
		global $ilCtrl;

		$this->gCtrl = $ilCtrl;
		$this->txt = $txt;
		$this->actions = $actions;

		$this->setId("my_observations_view");

		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		$this->initFilter();
		$this->determineSelectedFilters();

		if ($apply_filter) {
			$this->resetOffset();
			$this->writeFilterToSession();
		}

		if ($rest_filter) {
			$this->resetOffset();
			$this->resetFilter();
			$this->readFromSession();
		}

		$this->fillFilterValues();

		$this->setEnableTitle(true);
		$this->setEnableHeader(true);
		$this->setShowRowsSelector(false);
		$this->setTitle($this->txt("my_observations"));
		$this->setDescription($this->txt("my_observations_info"));
		$this->setDisableFilterHiding(true);

		$this->styles = array("table" => "tableReport");

		$this->setFormAction($ilCtrl->getFormAction($this->parent_obj));

		$this->setRowTemplate("tpl.talent_assessment_my_observations_view_row.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/TalentAssessment");

		$this->addColumn($this->txt("career_goal"), null);
		$this->addColumn($this->txt("fullname"), null);
		$this->addColumn($this->txt("org_unit"), null);
		$this->addColumn($this->txt("org_unit_supervisor"), null);
		$this->addColumn($this->txt("venue"), null);
		$this->addColumn($this->txt("start_date"), null);
		$this->addColumn($this->txt("observer"), null);
		$this->addColumn($this->txt("result"), null);
		$this->addColumn($this->txt("actions"), null);

		$this->in_progress = '<img src="'.\ilUtil::getImagePath("scorm/not_attempted.png").'" />';
		$this->passed = '<img src="'.\ilUtil::getImagePath("scorm/completed.png").'" />';
		$this->maybe = '<img src="'.\ilUtil::getImagePath("scorm/incomplete.png").'" />';
		$this->failed = '<img src="'.\ilUtil::getImagePath("scorm/failed.png").'" />';
	}

	public function fillRow($row)
	{
		$this->tpl->setVariable("CAREER_GOAL", $this->replaceEmptyWithMinus($row["title"]));
		$this->gCtrl->setParameterByClass("ilObjTalentAssessmentGUI", "ref_id", $row["ref_id"]);
		$ta_link = $this->gCtrl->getLinkTargetByClass(array("ilObjPluginDispatchGUI","ilObjTalentAssessmentGUI"), \ilTalentAssessmentObservationsGUI::CMD_OBSERVATIONS_LIST);
		$this->gCtrl->clearParametersByClass("ilObjTalentAssessmentGUI");
		$this->tpl->setVariable("TA_LINK", $this->replaceEmptyWithMinus($ta_link));
		$this->tpl->setVariable("NAME", $this->replaceEmptyWithMinus($row["lastname"])." ".$this->replaceEmptyWithMinus($row["firstname"]));
		$this->tpl->setVariable("ORG_UNIT", $this->replaceEmptyWithMinus($row["org_unit_title"]));
		$this->tpl->setVariable("ORG_UNIT_SUPERVISOR", $this->replaceEmptyWithMinus($row["supervisor"]));
		$this->tpl->setVariable("VENUE", $this->replaceEmptyWithMinus($row["venue_title"]));
		$this->tpl->setVariable("DATE", $this->replaceEmptyWithMinus($row["start_date_text"]));
		$this->tpl->setVariable("OBSERVER", $this->replaceEmptyWithMinus($row["observer"]));
		$this->tpl->setVariable("RESULT", $this->getResultImage($row["result"]));
		$this->tpl->setVariable("ACTIONS", $this->getActionMenu($row["ref_id"]));
	}

	protected function replaceEmptyWithMinus(string $token = null): string
	{
		if ($token == null || $token == "") {
			$token = "-";
		}
		return $token;
	}

	protected function getActionMenu($ref_id)
	{
		include_once("Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
		$current_selection_list = new \ilAdvancedSelectionListGUI();
		$current_selection_list->setAsynch(false);
		$current_selection_list->setAsynchUrl(true);
		$current_selection_list->setListTitle($this->txt("actions"));
		$current_selection_list->setId($ref_id);
		$current_selection_list->setSelectionHeaderClass("small");
		$current_selection_list->setItemLinkClass("xsmall");
		$current_selection_list->setLinksMode("il_ContainerItemCommand2");
		$current_selection_list->setHeaderIcon(\ilAdvancedSelectionListGUI::DOWN_ARROW_DARK);
		$current_selection_list->setUseImages(false);
		$current_selection_list->setAdditionalToggleElement("ref_id".$ref_id, "ilContainerListItemOuterHighlight");

		foreach ($this->getActionMenuItems($ref_id) as $key => $value) {
			$current_selection_list->addItem($value["title"], "", $value["link"], $value["image"], "", $value["frame"]);
		}

		return $current_selection_list->getHTML();
	}

	public function initFilter()
	{
		$item = $this->addFilterItemByMetaType("start_date", \ilTable2GUI::FILTER_DATE_RANGE, false, $this->txt("start_date"));

		require_once("Services/Form/classes/class.ilMultiSelectInputGUI.php");
		$item = new \ilMultiSelectInputGUI($this->txt("result"), "result");
		$item->setOptions($this->parent_obj->getResultOptions());
		$this->addFilterItem($item);

		$item = new \ilMultiSelectInputGUI($this->txt("title"), "career_goal");
		$item->setOptions($this->parent_obj->getCareerGoalOptions());
		$this->addFilterItem($item);

		$item = new \ilMultiSelectInputGUI($this->txt("org_unit"), "org_unit");
		$item->setOptions($this->parent_obj->getOrgUnitOptions());
		$this->addFilterItem($item);
	}

	protected function fillFilterValues()
	{
		foreach ($this->filters as $key => $value) {
			$this->filter[$value->getPostVar()] = $value->getValue();
		}
	}

	protected function readFromSession()
	{
		foreach ($this->filters as $key => $value) {
			$value->readFromSession();
		}
	}

	public function getFilterSettings()
	{
		return $this->filter;
	}

	protected function getActionMenuItems($ref_id)
	{
		$this->gCtrl->setParameter($this->parent_obj, "xtas_ref_id", $ref_id);
		$this->gCtrl->setParameter($this->parent_obj, "mode", $this->mode);
		$link_pdf = $this->memberlist_link = $this->gCtrl->getLinkTarget($this->parent_obj, "showPDF");
		$this->gCtrl->clearParameters($this->parent_obj);

		$this->gCtrl->setParameterByClass("ilObjTalentAssessmentGUI", "ref_id", $ref_id);
		$link_ta = $this->gCtrl->getLinkTargetByClass(array("ilObjPluginDispatchGUI", "ilObjTalentAssessmentGUI"), \ilTalentAssessmentObservationsGUI::CMD_OBSERVATIONS_LIST);
		$this->gCtrl->clearParametersByClass("ilObjTalentAssessmentGUI");

		$items = array();

		$vals = $this->actions->getObservationsCumulative(\ilObject::_lookupObjId($ref_id));
		if (!empty($vals)) {
			$items[] = array("title" => $this->txt("show_pdf"), "link" => $link_pdf, "image" => "", "frame"=>"");
		}

		$items[] = array("title" => $this->txt("open"), "link" => $link_ta, "image" => "", "frame"=>"");

		return $items;
	}

	protected function getResultImage($result)
	{
		switch ($result) {
			case \ilReportGUI::TA_IN_PROGRESS:
				return $this->in_progress;
				break;
			case \ilReportGUI::TA_PASSED:
				return $this->passed;
				break;
			case \ilReportGUI::TA_MAYBE:
				return $this->maybe;
				break;
			case \ilReportGUI::TA_FAILED:
				return $this->failed;
				break;
		}
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
}

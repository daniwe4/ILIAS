<?php

namespace CaT\Plugins\TalentAssessment\Observations;

require_once("Services/Table/classes/class.ilTable2GUI.php");

class ilObservationsOverviewTableGUI extends \ilTable2GUI
{
	use ilFormHelper;

	public function __construct($a_parent_obj, array $values, array $observer, $a_parent_cmd = "", $a_template_context = "")
	{
		$this->txt = $a_parent_obj->getTXTClosure();
		$this->values = $values;
		$this->observer = $observer;

		$this->setId("tlnt_assmnt_obsrvtns_ovrvw_tbl");

		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		$this->setEnableHeader(true);
		$this->setEnableTitle(false);
		$this->setShowRowsSelector(false);
		$this->setEnableNumInfo(false);
		$this->setRowTemplate("tpl.talent_assessment_observations_overview_row.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/TalentAssessment");

		$this->addColumn($values["title"], null, "25%");

		$obs_width = floor(75 / count($observer));
		foreach ($observer as $value) {
			$this->addColumn($value->getLastname()." ".$value->getFirstname(), null, $obs_width."%");
		}

		$this->setData($values["requirements"]);
	}

	public function fillRow($row)
	{
		$this->tpl->setVariable("TITLE", $row["title"]);
		foreach ($this->observer as $value) {
			$this->tpl->setCurrentBlock("points");

			if (array_key_exists($value->getId(), $row["observer"])) {
				$this->tpl->setVariable("POINTS", $row["observer"][$value->getId()]);
			} else {
				$this->tpl->setVariable("POINTS", "-");
			}

			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	 * @param 	string	$code
	 * @return	string
	 */
	public function txt($code)
	{
		assert('is_string($code)');

		$txt = $this->txt;

		return $txt($code);
	}
}

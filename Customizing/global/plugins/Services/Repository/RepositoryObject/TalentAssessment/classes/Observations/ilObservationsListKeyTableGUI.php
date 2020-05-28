<?php

namespace CaT\Plugins\TalentAssessment\Observations;

require_once("Services/Table/classes/class.ilTable2GUI.php");
require_once("Services/UICore/classes/class.ilTemplate.php");

class ilObservationsListKeyTableGUI extends \ilTable2GUI
{
	use ilFormHelper;

	const SI_PREFIX = "req_id";

	public function __construct($a_parent_obj, array $values, $a_parent_cmd = "", $a_template_context = "")
	{
		global $DIC;

		$this->g_ctrl = $DIC->ctrl();
		$this->txt = $a_parent_obj->getTXTClosure();
		$this->values = $values;
		$this->possible_cmd = $a_parent_obj->getPossibleCMD();

		$this->setId("ta_ass_observ_list_key_tbl");

		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		$this->setEnableHeader(true);
		$this->setEnableTitle(false);
		$this->setShowRowsSelector(false);
		$this->setEnableNumInfo(false);

		$this->g_ctrl->setParameter($this->parent_obj, "obs_id", $values["obs_id"]);
		$this->setFormAction($this->g_ctrl->getFormAction($this->parent_obj));
		$this->g_ctrl->clearParameters($this->parent_obj);

		$this->setRowTemplate("tpl.talent_assessment_observations_key_table_row.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/TalentAssessment");

		$this->addColumn($this->txt("points"), null);
		$this->addColumn($this->txt("description"), null);

		$this->setData($values);
	}

	public function fillRow($row)
	{
		$this->tpl->setVariable("POINTS", $row["points"]);
		$this->tpl->setVariable("DESCRIPTION", $this->txt($row["description"]));
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

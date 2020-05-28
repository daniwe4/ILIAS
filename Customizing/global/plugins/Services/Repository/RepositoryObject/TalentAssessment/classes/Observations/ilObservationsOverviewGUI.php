<?php

namespace CaT\Plugins\TalentAssessment\Observations;

class ilObservationsOverviewGUI
{
	public function __construct($parent_obj)
	{
		$this->parent_obj = $parent_obj;
	}

	public function render()
	{
		$obj_id = $this->parent_obj->getObjId();
		$actions = $this->parent_obj->getActions();

		$observer = $actions->getAssignedUsers($obj_id);

		if (!empty($observer)) {
			$obs = $actions->getObservationOverviewData($obj_id, $observer);
			$html = "";

			foreach ($obs as $key => $ob) {
				$gui = new ilObservationsOverviewTableGUI($this->parent_obj, $ob, $observer);
				$html .= $gui->getHtml();
			}

			return $html;
		} else {
			\ilUtil::sendInfo($this->parent_obj->txt("no_observer_no_overview"));
			return "";
		}
	}
}

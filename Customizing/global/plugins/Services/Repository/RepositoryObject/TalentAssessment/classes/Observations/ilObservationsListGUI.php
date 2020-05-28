<?php

namespace CaT\Plugins\TalentAssessment\Observations;

class ilObservationsListGUI
{
	public function __construct($parent_obj)
	{
		$this->parent_obj = $parent_obj;
	}

	public function render()
	{
		$obs = $this->parent_obj->getActions()->getObservationListData($this->parent_obj->getObjId());
		$html = "";

		foreach ($obs as $key => $ob) {
			$tpl_marker = new \ilTemplate("tpl.talent_assessment_jump_marker.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/TalentAssessment/");
			$tpl_marker->setVariable("IDENTIFIER", $key);
			$gui = new ilObservationsListTableGUI($this->parent_obj, $ob, $key);

			$html .= $tpl_marker->get();
			$html .= $gui->getHtml();
		}

		$html.= $this->renderKey();

		return $html;
	}

	protected function renderKey()
	{
		$key = array(array("points"=>1, "description"=>"points_description_1")
					  , array("points"=>2, "description"=>"points_description_2")
					  , array("points"=>3, "description"=>"points_description_3")
					  , array("points"=>4, "description"=>"points_description_4")
					  , array("points"=>5, "description"=>"points_description_5")
				);
		$gui = new ilObservationsListKeyTableGUI($this->parent_obj, $key);
		return $gui->getHtml();
	}
}

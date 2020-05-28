<?php

namespace CaT\Plugins\TalentAssessment\Observations;
use \CaT\Plugins\TalentAssessment\ilActions;
use \CaT\Plugins\TalentAssessment\Settings\TalentAssessment;

class ilReportPreviewGUI
{

	public function __construct(TalentAssessment $settings, ilActions $actions, \Closure $txt)
	{
		$this->settings = $settings;
		$this->actions = $actions;
		$this->txt = $txt;
	}

	public function render($svg = true)
	{
		$tpl = new \ilTemplate("tpl.ta_report.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/TalentAssessment");

		$tpl->setVariable("TA_TITLE", $this->actions->getCareerGoalTitle($this->settings->getCareerGoalId()));

		$tpl->setVariable("SUB_FIRST", $this->txt("fullname"));
		$tpl->setVariable("SUB_SECOND", $this->txt("org_unit"));
		$tpl->setVariable("SUB_THIRD", $this->txt("date"));

		$tpl->setVariable("VALUE_FIRST", $this->settings->getFirstname()." ".$this->settings->getLastname());
		$tpl->setVariable("VALUE_SECOND", $this->actions->getOrgUnitTitle($this->settings->getOrgUnit()));
		$tpl->setVariable("VALUE_THIRD", $this->settings->getStartdate()->get(IL_CAL_FKT_DATE, "d.m.Y"));

		$gui = new ilObservationsDiagrammGUI($this->settings, $this->actions, $this->txt);
		$graph = $gui->getSVGData();

		if ($svg) {
			$tpl->setCurrentBlock("diagramm_svg");
			$tpl->setVariable("DIAGRAMM", $graph);
			$tpl->parseCurrentBlock();
		} else {
			$svg_converter = new SVGConverter();
			$destination = $svg_converter->convertAndReturnPath($graph);

			$tpl->setCurrentBlock("diagramm_png");
			$tpl->setVariable("DIAGRAMM", $destination);
			$tpl->parseCurrentBlock();
		}


		$tpl->setVariable("SUMMARY_TITLE", $this->txt("pdf_summary_title"));

		$tpl->setVariable("SUMMARY", $this->settings->getResultComment());

		$judgement_text = $this->settings->getTextForPotential();
		$judgement_text = $this->fillPlaceholder($judgement_text);
		$tpl->setVariable("RESULT", $judgement_text);

		return $tpl->get();
	}

	protected function fillPlaceholder($judgement_text)
	{
		$judgement_text = str_replace("[VORNAME]", $this->settings->getFirstname(), $judgement_text);
		$judgement_text = str_replace("[NACHNAME]", $this->settings->getLastname(), $judgement_text);
		$judgement_text = str_replace("[KARRIEREZIEL]", $this->actions->getCareerGoalTitle($this->settings->getCareerGoalId()), $judgement_text);

		return $judgement_text;
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

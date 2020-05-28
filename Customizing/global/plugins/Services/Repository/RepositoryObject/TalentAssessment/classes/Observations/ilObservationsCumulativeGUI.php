<?php

namespace CaT\Plugins\TalentAssessment\Observations;

require_once("Services/UICore/classes/class.ilTemplate.php");

/**
 * This is a cumulative view of all results to observations and requirements
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilObservationsCumulativeGUI
{
	public function __construct(\ilTalentAssessmentObservationsGUI $parent_obj)
	{
		global $DIC;

		$this->g_tpl = $DIC->ui()->mainTemplate();
		$this->parent_obj = $parent_obj;
		$this->g_tpl->addCSS("Customizing/global/plugins/Services/Repository/RepositoryObject/TalentAssessment/templates/css/talent_assessment_observation_cummulative_table.css");
	}

	/**
	 * Render the cumulative view
	 *
	 * @return null
	 */
	public function render()
	{
		$obj_id = $this->parent_obj->getObjId();
		$actions = $this->parent_obj->getActions();

		$observer = $actions->getAssignedUsers($obj_id);

		if (empty($observer)) {
			\ilUtil::sendInfo($this->parent_obj->txt("no_observer_no_cumulative", false));
			return "";
		}

		$obs = $actions->getObservationsCumulative($obj_id);
		$req_res = $actions->getRequestresultCumulative(array_keys($obs));

		$col_span = count($observer);

		$tpl = new \ilTemplate("tpl.talent_assessment_observations_cumulative.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/TalentAssessment");

		$this->renderObservations($obs, $tpl, $col_span);

		$this->renderObserver($observer, $tpl, count($obs));

		$this->renderResults($req_res, $obs, $observer, $tpl);

		$this->renderMiddleValue($req_res, $obs, $tpl, $col_span);

		return $tpl->get();
	}

	/**
	 * Render row for observations
	 *
	 * @param string[]		$obs
	 * @param \ilTemplate 	$tpl
	 * @param int 			$col_span
	 *
	 * @return null
	 */
	protected function renderObservations(array $obs, \ilTemplate $tpl, $col_span)
	{
		foreach ($obs as $key => $title) {
			$tpl->setCurrentBlock("observations");
			$tpl->setVariable("COL_SPAN_HEAD", $col_span);
			$tpl->setVariable("OBS_TITLE", $title);
			$tpl->parseCurrentBlock();
		}
	}

	/**
	 * Render row of of observer
	 *
	 * @param \ilObjUser[]	$observer
	 * @param \ilTemplate 	$tpl
	 * @param int 			$obs_count
	 *
	 * @return null
	 */
	protected function renderObserver($observer, \ilTemplate $tpl, $obs_count)
	{
		$observer_count = count($observer);
		$pad_small = 80 / $obs_count / $observer_count / 4 - 0.6;
		$pad_big = 80 / $obs_count / $observer_count / 4 + 1.1;

		for ($i = 0; $i < $obs_count; $i++) {
			foreach ($observer as $key => $value) {
				$firstname = $value->getFirstname();
				$lastname = $value->getLastname();

				$tpl->setCurrentBlock("observer");
				if (strlen($lastname.$firstname) > 25) {
					$tpl->setVariable("OBSERVER_LASTNAME", "<pre>".$lastname.", </pre><pre>". $firstname."</pre>");
					$tpl->setVariable("pad", $pad_small);
				} else {
					$tpl->setVariable("OBSERVER_LASTNAME", $lastname . ", ");
					$tpl->setVariable("OBSERVER_FIRSTNAME", $firstname);
					$tpl->setVariable("pad", $pad_big);
					$tpl->parseCurrentBlock();
				}
			}
		}
	}

	/**
	 * Render row with results each requirement
	 *
	 * @param string[]		$req_res
	 * @param string[]		$obs
	 * @param \ilObjUser[]	$observer
	 * @param \ilTemplate	$tpl
	 *
	 * @return null
	 */
	protected function renderResults($req_res, $obs, $observer, $tpl)
	{
		$printed = array();
		$i = 0;

		foreach ($req_res as $req_title => $req) {
			$html = "";

			if ($printed[$req_title]) {
				continue;
			}
			$pts_tpl = new \ilTemplate("tpl.talent_assessment_observations_cumulative_pts.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/TalentAssessment", false, false);

			if ($i % 2 == 0) {
				$row_class = "row_grey";
			} else {
				$row_class = "row_norm";
			}

			$pts_tpl->setCurrentBlock("tr_start");
			$pts_tpl->setVariable("CSS_ROW", $row_class);
			$pts_tpl->parseCurrentBlock();

			$i++;

			foreach ($obs as $obs_key => $title) {
				foreach ($observer as $usr) {
					$pts = $this->getPointsFor($req_res, $req_title, $obs_key, $usr->getId());

					$pts_tpl->setCurrentBlock("pts");
					$pts_tpl->setVariable("POINTS", $pts);
					$pts_tpl->parseCurrentBlock();
				}

				$pts_tpl->setVariable("POINTS_MIDDLE", round($req["middle"], 1));
			}

			$pts_tpl->setVariable("REQ_TITLE", $req_title);

			$pts_tpl->touchBlock("tr_end");
			$html .= $pts_tpl->get();

			$tpl->setCurrentBlock("tr_requirement");
			$tpl->setVariable("PTS_ROW", $html);
			$tpl->parseCurrentBlock();

			$printed[$req_title] = true;
		}
	}

	/**
	 * Render row of results middle values
	 *
	 * @param string[]		$req_res
	 * @param string[]		$obs
	 * @param \ilTemplate 	$tpl
	 *
	 * @return null
	 */
	protected function renderMiddleValue($req_res, $obs, $tpl, $col_span)
	{
		$middle_total = 0;
		foreach ($obs as $key => $title) {
			$middle = $this->getObservationMiddle($req_res, $key);
			$middle_total += $middle;

			$tpl->setCurrentBlock("bla");
			$tpl->setVariable("COL_SPAN_FOOTER", $col_span);
			$tpl->setVariable("PTS", round($middle, 1));
			$tpl->parseCurrentBlock();
		}

		$tpl->setVariable("MIDDLE_TOTAL", round(($middle_total / count($obs)), 1));
	}

	/**
	 * Get the points observor gives for requirement
	 *
	 * @param string 	$req
	 * @param string 	$title
	 * @param int 		$obs_id
	 * @param int 		$usr_id
	 *
	 * @return int | string
	 */
	protected function getPointsFor($req, $title, $obs_id, $usr_id)
	{
		$observer = $req[$title][$obs_id]["observer"];

		if (is_array($observer) && array_key_exists($usr_id, $observer)) {
			return $observer[$usr_id];
		}

		return "-";
	}

	/**
	 * Return middle value of observation
	 *
	 * @param string[]	$req_res
	 * @param int 		$obs_id
	 *
	 * @return float
	 */
	protected function getObservationMiddle($req_res, $obs_id)
	{
		$sum = 0;
		$counter = 0;

		foreach ($req_res as $key => $req) {
			if (array_key_exists($obs_id, $req)) {
				foreach ($req[$obs_id]["observer"] as $value) {
					$sum += $value;
					$counter++;
				}
			}
		}

		return $sum / $counter;
	}
}

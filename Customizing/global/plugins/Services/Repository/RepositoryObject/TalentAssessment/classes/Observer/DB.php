<?php

namespace CaT\Plugins\TalentAssessment\Observer;

interface DB
{
	/**
	 * create local role template
	 *
	 * @param string 	$template_title
	 * @param string 	$tpl_description
	 */
	public function createLocalRoleTemplate($tpl_title, $tpl_description);

	/**
	 * get obj id of role template
	 *
	 * @param string 	$tpl_title
	 *
	 * @return int
	 */
	public function getRoltId($tpl_title);
}

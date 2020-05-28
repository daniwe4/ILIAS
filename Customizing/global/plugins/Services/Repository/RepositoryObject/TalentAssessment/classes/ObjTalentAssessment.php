<?php

namespace CaT\Plugins\TalentAssessment;

/**
 * Inteface for basic TalentAssessment object to get it more testable
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface ObjTalentAssessment
{
	/**
	 * Get the title of talent assessment
	 *
	 * @return string
	 */
	public function getTitle();
	/**
	 * Get the description of talent assessment
	 *
	 * @return string
	 */
	public function getDescription();
	/**
	 * Set the title
	 *
	 * @param string 	$a_title
	 *
	 * @return null
	 */
	public function setTitle($a_title);
	/**
	 * Set the description
	 *
	 * @param string 	$a_desc
	 *
	 * @return null
	 */
	public function setDescription($a_desc);
	/**
	 * Update talent assessment
	 *
	 * @return null
	 */
	public function update();
	/**
	 * Update the additional settings like is_online or else
	 *
	 * @param \Closure 	$update
	 *
	 * @return null
	 */
	public function updateSettings(\Closure $update);
	/**
	 * Get the addition settings
	 *
	 * @return CaT\Plugins\TalentAssessment\Settings\TalentAssessment
	 */
	public function getSettings();
}

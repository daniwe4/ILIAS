<?php

namespace CaT\Plugins\TalentAssessmentReport\Settings;

/**
 * Defines working with settings in database
 *
 * @author 	Stefan Hecken 	<stefan.hecken@concepts-anf-training.de>
 */
interface DB
{
	/**
	 * Create tables or else
	 *
	 * @param return null
	 */
	public function install();

	/**
	 * Create new setting entries
	 *
	 * @param int 	$obj_id
	 * @param bool 	$is_admin
	 * @param bool 	$is_online
	 *
	 * @return TalentAssessmentReport
	 */
	public function create($obj_id, $is_admin, $is_online);

	/**
	 * Update existing settings
	 *
	 * @param TalentAssessmentReport 	$settings
	 *
	 * @return null
	 */
	public function update(TalentAssessmentReport $settings);

	/**
	 * Get settings for id
	 *
	 * @param int 	$obj_id
	 *
	 * @throws LogicException 	if no settings are found
	 *
	 * @return TalentAssessmentReport
	 */
	public function selectFor($obj_id);

	/**
	 * Delete settings for id
	 *
	 * @param int 	$obj_id
	 *
	 * @return null
	 */
	public function deleteFor($obj_id);
}

<?php

namespace CaT\Plugins\CareerGoal\Observations;
use CaT\Plugins\CareerGoal\Requirements;

interface DB
{
	/**
	 * create new requirement
	 *
	 * @param 	int 			$career_goal_id
	 * @param 	int 			$title
	 * @param 	int 			$description
	 * @param 	int[]	$requirements
	 *
	 * return Requirement
	 */
	public function create($career_goal_id, $title, $description, array $requirements);

	/**
	 * update a observation
	 *
	 * @param 	Requirement 	$observations
	 */
	public function update(Observation $observation);

	/**
	 * select values of observation for $obj_id
	 *
	 * @param 	int 			$obj_id
	 */
	public function select($obj_id);

	/**
	 * delete observation for $obj_id
	 *
	 * @param 	int 			$obj_id
	 */
	public function delete($obj_id);

	/**
	 * get next obj id
	 *
	 * @return 	int 			$obj_id
	 */
	public function getObjId();

	/**
	 * get alls observations for career goal id
	 *
	 * @param 	int 			$career_goal_id
	 *
	 * @return 	Requirement[]
	 */
	public function selectObservationsFor($career_goal_id);

	/**
	 * get data for observations list
	 *
	 * @param 	int 			$career_goal_id
	 *
	 * @return 	array[]
	 */
	public function getListData($career_goal_id);

	/**
	 * get data for talent assessment observation list if not started
	 *
	 * @param 	int 			$career_goal_id
	 *
	 * @return 	array[]
	 */
	public function getTAListData($career_goal_id);
}

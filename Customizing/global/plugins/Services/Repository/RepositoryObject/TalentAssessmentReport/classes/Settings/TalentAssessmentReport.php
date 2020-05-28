<?php

namespace CaT\Plugins\TalentAssessmentReport\Settings;

/**
 * Object for settings of report excluding title and description
 *
 * @author 	Stefan Hecken	<stefan.hecken@concepts-and-training.de>
 */
class TalentAssessmentReport
{
	/**
	 * @var int
	 */
	protected $obj_id;

	/**
	 * @var bool
	 */
	protected $is_admin;

	/**
	 * @var bool
	 */
	protected $is_online;

	/**
	 * @param int 	$obj_id
	 * @param bool 	$is_admin
	 * @param bool 	$is_online
	 */
	public function __construct($obj_id, $is_admin, $is_online)
	{
		assert('is_int($obj_id)');
		assert('is_bool($is_admin)');
		assert('is_bool($is_online)');

		$this->obj_id = $obj_id;
		$this->is_admin = $is_admin;
		$this->is_online = $is_online;
	}

	/**
	 * Get the obj id
	 *
	 * @return int
	 */
	public function getObjId()
	{
		return $this->obj_id;
	}

	/**
	 * Get the report is only for admins
	 *
	 * @return bool
	 */
	public function getIsAdmin()
	{
		return $this->is_admin;
	}

	/**
	 * Get the report is online or not
	 *
	 * @return bool
	 */
	public function getIsOnline()
	{
		return $this->is_online;
	}

	/**
	 * Get a cloned object with new is admin configuration
	 *
	 * @param bool 	$is_admin
	 *
	 * @return null
	 */
	public function withIsAdmin($is_admin)
	{
		assert('is_bool($is_admin)');
		$clone = clone $this;
		$clone->is_admin = $is_admin;
		return $clone;
	}

	/**
	 * Get a cloned object with new is online configuration
	 *
	 * @param bool 	$is_online
	 *
	 * @return null
	 */
	public function withIsOnline($is_online)
	{
		assert('is_bool($is_online)');
		$clone = clone $this;
		$clone->is_online = $is_online;
		return $clone;
	}
}

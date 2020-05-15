<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

class ilDataChangesPlugin extends ilCronHookPlugin
{
	public function __construct()
	{
		parent::__construct();
	}

	function getPluginName() : string
	{
		return "DataChanges";
	}

	/**
	 * Get an array with 1 to n numbers of cronjob objects
	 *
	 * @return ilJob[]
	 */
	public function getCronJobInstances() : array
	{
		return [];
	}

	public function getCronJobInstance($a_job_id) : ilCronJob
	{
	}

	public function txtClosure() : Closure
	{
		return function (string $code) {
			return $this->txt($code);
		};
	}
}

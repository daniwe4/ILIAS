<?php

namespace CaT\Plugins\TalentAssessmentReport;

class ilActions
{
	public function __construct($object, $settings_db, $report_db)
	{
		$this->object = $object;
		$this->settings_db = $settings_db;
		$this->report_db = $report_db;
	}

	public function update($title, $description, $is_admin, $is_online)
	{
		assert('is_string($title)');
		assert('is_string($description)');
		assert('is_bool($is_admin)');
		assert('is_bool($is_online)');

		$this->object->setTitle($title);
		$this->object->setDescription($description);

		$this->object->updateSettings(function ($s) use ($is_admin, $is_online) {
				return $s->withIsAdmin($is_admin)
						 ->withIsOnline($is_online);
		});

		$this->object->update();
	}

	public function getObject()
	{
		if ($this->object === null) {
			throw new \Exception(__METHOD__." no object is set.");
		}

		return $this->object;
	}

	public function getAssessmentsData($filter_values)
	{
		return $this->report_db->getAssessmentsData($filter_values);
	}

	public function getObservationsCumulative($obj_id)
	{
		return $this->report_db->getObservationsCumulative($obj_id);
	}

	public function getCareerGoalsOptions()
	{
		return $this->report_db->getCareerGoalsOptions();
	}

	public function getAllObserver()
	{
		return $this->report_db->getAllObserver();
	}
}

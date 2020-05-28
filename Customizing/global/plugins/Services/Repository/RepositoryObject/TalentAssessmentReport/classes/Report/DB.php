<?php

namespace CaT\Plugins\TalentAssessmentReport\Report;

interface DB
{
	public function getAssessmentsData(array $filter_values);
}

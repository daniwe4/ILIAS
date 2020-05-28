<?php

declare(strict_types=1);

namespace CaT\Plugins\TalentAssessment\Settings;

interface DB
{
	public function create(
		int $obj_id,
		int $state,
		int $career_goal_id,
		string $username,
		string $firstname,
		string $lastname,
		string $email,
		\ilDateTime $start_date = null,
		\ilDateTime $end_date = null,
		string $venue = null,
		string $org_unit = null,
		bool $started,
		float $lowmark,
		float $should_specification,
		float $potential,
		string $result_comment,
		string $default_text_failed,
		string $default_text_partial,
		string $default_text_success
	): TalentAssessment;

	public function update(TalentAssessment $settings);

	public function delete(int $obj_id);

	public function select(int $obj_id);

	public function getCareerGoalsOptions(): array;
}

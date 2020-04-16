<?php

/**
 * this mimics the relevant parts of ilias' ilCronManager,
 * including data to test against.
 */
interface ilCronManager
{
};
class mockCronMan implements \ilCronManager
{
    public $jobs = array();
    public $schedule_types = array(
        1 => 'P1D', //ilCronJob::SCHEDULE_TYPE_DAILY
        2 => 'PT%dM', //ilCronJob::SCHEDULE_TYPE_IN_MINUTES
        3 => 'PT%dH', //ilCronJob::SCHEDULE_TYPE_IN_HOURS
        4 => 'P%dD', //ilCronJob::SCHEDULE_TYPE_IN_DAYS
        5 => 'P1W', //ilCronJob::SCHEDULE_TYPE_WEEKLY
        6 => 'P1M', //ilCronJob::SCHEDULE_TYPE_MONTHLY
        7 => 'P3M', //ilCronJob::SCHEDULE_TYPE_QUARTERLY
        8 => 'P1Y', //ilCronJob::SCHEDULE_TYPE_YEARLY
    );

    public function __construct()
    {
        $this->jobs['d1_finished_1h_ago'] = array(
            'schedule_type' => 1, //daily
            'schedule_value' => 1,
            'job_result_ts' => date_create('now')->format('U'), //->sub(new \DateInterval('PT1H'))->format('U'),
            'running_ts' => 0,
            'alive_ts' => 0,
            'job_status' => 1,

            'test_should_fail' => false
        );
        $this->jobs['h1_finished_2h_ago'] = array(
            'schedule_type' => 3, //hourly
            'schedule_value' => 1,
            'job_result_ts' => date_create('now')->sub(new \DateInterval('PT2H'))->format('U'),
            'running_ts' => 0,
            'alive_ts' => 0,
            'job_status' => 1,

            'test_should_fail' => true
        );
        $this->jobs['m10_running_since_20m'] = array(
            'schedule_type' => 2, //minutes
            'schedule_value' => 10,
            'job_result_ts' => date_create('now')->sub(new \DateInterval('PT30M'))->format('U'),
            'running_ts' => date_create('now')->sub(new \DateInterval('PT20M'))->format('U'),
            'alive_ts' => date_create('now')->sub(new \DateInterval('PT20M'))->format('U'),
            'job_status' => 1,

            'test_should_fail' => true //with tolerance < 20
        );

        $this->jobs['y1_never_ran_or_reset'] = array(
            'schedule_type' => 8, //yearly
            'schedule_value' => 10,
            'job_result_ts' => 0,
            'running_ts' => 0,
            'alive_ts' => 0,
            'job_status' => 1,

            'test_should_fail' => true
        );
        $this->jobs['y1_ran_before'] = array(
            'schedule_type' => 8, //yearly
            'schedule_value' => 10,
            'job_result_ts' => date_create('now')->sub(new \DateInterval('P11M'))->format('U'),
            'running_ts' => 0,
            'alive_ts' => 0,
            'job_status' => 1,

            'test_should_fail' => false
        );

        $this->jobs['deactivated'] = array(
            'schedule_type' => 1,
            'schedule_value' => 1,
            'job_result_ts' => date_create('now')->sub(new \DateInterval('P11M'))->format('U'),
            'running_ts' => 0,
            'alive_ts' => 0,
            'job_status' => 0,

            'test_should_fail' => true
        );
    }

    /**
     * mock-implementation: get job infos
     */
    public function getCronJobData($id)
    {
        if (!array_key_exists($id, $this->jobs)) {
            return array();
        }
        return array($this->jobs[$id]);
    }

    /**
     * helper-function for tests
     */
    public function getJobIds()
    {
        return array_keys($this->jobs);
    }

    /**
     * helper-function for tests
     */
    public function getScheduleTypes()
    {
        return $this->schedule_types;
    }
}

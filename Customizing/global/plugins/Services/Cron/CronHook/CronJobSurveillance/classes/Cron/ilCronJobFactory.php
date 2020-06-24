<?php
namespace CaT\Plugins\CronJobSurveillance\Cron;

/**
 * This is the facade for ILIAS.
 *
 * @author Nils Haagen 	<nils.haagen@concepts-and-training.de>
 */
class ilCronJobFactory implements CronJobFactory
{
    private $cron_man;
    private $schedule_types = array();

    const DB_FIELD_SCHEDULE_TYPE = 'schedule_type';
    const DB_FIELD_SCHEDULE_VALUE = 'schedule_value';
    const DB_FIELD_RUNNING = 'running_ts';
    const DB_FIELD_ALIVE = 'alive_ts';
    const DB_FIELD_LAST_RUN = 'job_result_ts';
    const DB_FIELD_JOB_STATUS = 'job_status';
    const DB_FIELD_COMPONENT = 'component';

    const TIMEZONE_TO_CHECK_AGAINST = 'Europe/Berlin';

    /**
     * @param CronManager $cron_manager
     * @param array<mixed, string> 	$schedule_types 	values must be in interval-definition-format, potentially as templates (e.g: "PT%dM" for x minutes)
     */
    public function __construct(CronManager $cron_manager, array $schedule_types)
    {
        $this->cron_man = $cron_manager;
        $this->schedule_types = $schedule_types;
    }

    /**
     * @inheritdoc
     */
    public function getCronJob(string $id)
    {
        //jobdata is assoc-array from db-row (can be empty)
        $jobdata = $this->cron_man->getCronJobData($id);

        if (!$jobdata) {
            return;
        }
        $jobdata = $jobdata[0];
        $schedule_type = $jobdata[self::DB_FIELD_SCHEDULE_TYPE];
        $schedule_value = $jobdata[self::DB_FIELD_SCHEDULE_VALUE];

        if (is_null($schedule_type) && is_null($schedule_value)) { //job has no flexible schedule
            list($schedule_type, $schedule_value) = $this->cron_man->getFixScheduleForJob($id, $jobdata[self::DB_FIELD_COMPONENT]);
        }

        if ((int) $jobdata[self::DB_FIELD_JOB_STATUS] === 0 ||
            (is_null($schedule_type) && is_null($schedule_value))
        ) { //job is inactive or went away somehow.
            return null;
        }

        $interval = $this->buildInterval(
            (int) $schedule_type,
            (int) $schedule_value
        );

        $finished = ((int) $jobdata[self::DB_FIELD_RUNNING] + (int) $jobdata[self::DB_FIELD_ALIVE]) === 0;
        $last_start = $jobdata[self::DB_FIELD_LAST_RUN];
        if (!is_null($last_start)) {
            $last_start = $this->buildDateTimeFromTS($last_start);
        }

        return new CronJob($id, $interval, $finished, $last_start);
    }

    /**
     * @param 	int 	$schedule_type
     * @param 	int 	$schedule_value
     * @return 	DateInterval
     */
    private function buildInterval(int $schedule_type, int $schedule_value)
    {
        $format = $this->schedule_types[$schedule_type];
        $interval = new \DateInterval(sprintf($format, $schedule_value));
        return $interval;
    }

    /**
     * @param 	string 	$ts 	timestamp
     * @return 	DateTime
     */
    private function buildDateTimeFromTS($ts)
    {
        $dat = new \DateTime('@' . $ts);
        $dat->setTimezone(new \DateTimeZone(self::TIMEZONE_TO_CHECK_AGAINST));
        return $dat;
    }
}

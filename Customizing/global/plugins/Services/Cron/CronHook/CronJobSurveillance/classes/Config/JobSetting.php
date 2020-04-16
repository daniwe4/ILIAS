<?php
namespace CaT\Plugins\CronJobSurveillance\Config;

/**
 *
 * @author Nils Haagen 	<nils.haagen@concepts-and-training.de>
 */
class JobSetting
{
    /**
     * @var 	string
     */
    protected $job_id;

    /**
     * @var 	int
     */
    protected $tolerance;

    /**
     * @param 	string 	$id
     * @param 	int 	$tolerance
     * @return 	JobSetting
     */
    public function __construct($id, $tolerance = 0)
    {
        assert('is_string($id)');
        assert('is_int($tolerance)');

        $this->job_id = $id;
        $this->tolerance = $tolerance;
    }

    /**
     *
     * @return 	string
     */
    public function getJobId()
    {
        return $this->job_id;
    }

    /**
     * Tolerance, or "estimated job time", allows a job to take longer
     * than the time determined by the next scheduled execution.
     * This returns the amount of minutes.
     *
     * @return 	int
     */
    public function getTolerance()
    {
        return $this->tolerance;
    }
}

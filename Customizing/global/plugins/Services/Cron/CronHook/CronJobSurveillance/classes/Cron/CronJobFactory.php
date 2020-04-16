<?php
namespace CaT\Plugins\CronJobSurveillance\Cron;

/**
 * Classes implementing this interface are responsible for providing
 * CronJobs as used in this plugin.
 *
 * @author Nils Haagen 	<nils.haagen@concepts-and-training.de>
 */
interface CronJobFactory
{

    /**
     * @param 	string 	$id
     * @return 	CronJob | null
     */
    public function getCronJob($id);
}

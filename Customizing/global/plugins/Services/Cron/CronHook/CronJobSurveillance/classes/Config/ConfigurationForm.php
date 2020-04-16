<?php
namespace CaT\Plugins\CronJobSurveillance\Config;

use CaT\Plugins\CronJobSurveillance\Cron\CronManager;

/**
 * Build a configuration form.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class ConfigurationForm
{
    const F_JOB_ID = "job_id";
    const F_TOLERANCE = "tolerance";

    public function __construct(CronManager $cron_manager, \Closure $txt)
    {
        $this->cron_manager = $cron_manager;
        $this->txt = $txt;
    }

    /**
     * Build a form.
     *
     * @return 	string
     */
    public function build(FormBuilder $fb)
    {
        $fb->addCJSConfigHeaderGUI($this->txt("job_id"), $this->txt("tolerance"));
        $fb->addCJSConfigItemGUI(
            $this->cron_manager->getPossibleJobsToTakeUnderSurveillance(),
            $this
        );
        $fb->addButton("save", $this->txt("submit"), "saveConfig");
        $fb->addButton("cancel", $this->txt("cancel"), "cancelConfig");
        $fb->addButton("delAll", $this->txt("delete_all"), "deleteAll");
        return $fb->getForm("CronJobs", "ilCJSConfigGUI");
    }

    /**
     * Generate an array of JobSetting objects.
     *
     * @param 	array $values
     * @return 	array
     */
    public function genrateJobSettingsArray(array $values)
    {
        $result = array();

        if (isset($values[ConfigurationForm::F_JOB_ID])) {
            foreach ($values[ConfigurationForm::F_JOB_ID] as $key => $job_id) {
                $result[] = new JobSetting(
                    $job_id,
                    (int) $values[ConfigurationForm::F_TOLERANCE][$key]
                );
            }
        }

        return $result;
    }

    /**
     * Translate code to lang value
     *
     * @param 	string 	$code
     * @return 	string
     */
    protected function txt($code)
    {
        assert('is_string($code)');

        $txt = $this->txt;

        return $txt($code);
    }
}

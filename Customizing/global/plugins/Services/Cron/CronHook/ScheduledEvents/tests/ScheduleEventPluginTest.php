<?php

use PHPUnit\Framework\TestCase;
use CaT\Plugins\ScheduledEvents as PSE;
use ILIAS\TMS\ScheduledEvents as TSE;

/**
 * @group needsInstalledILIAS
 */
class ScheduleEventPluginTest extends TestCase
{
    public function setUp() : void
    {
        require_once 'Customizing/global/plugins/Services/Cron/CronHook/ScheduledEvents/classes/class.ilScheduledEventsPlugin.php';
        require_once 'Services/PHPUnit/classes/class.ilUnitUtil.php';
        require_once 'Customizing/global/plugins/Services/Cron/CronHook/ScheduledEvents/classes/class.ilScheduledEventsJob.php';
        ilUnitUtil::performInitialisation();
    }

    public function test_init()
    {
        $plugin = new ilScheduledEventsPlugin();
        $this->assertInstanceOf(ilScheduledEventsPlugin::class, $plugin);
        return $plugin;
    }

    /**
     * @depends test_init
     */
    public function test_db()
    {
        $plugin = new ilScheduledEventsPlugin();
        $this->assertInstanceOf(PSE\ilActions::class, $plugin->getActions());
    }

    /**
     * @depends test_init
     */
    public function test_cron_job_instance()
    {
        $plugin = new ilScheduledEventsPlugin();
        $this->assertInstanceOf(\ilScheduledEventsJob::class, $plugin->getCronJobInstance(\ilScheduledEventsJob::ID));
    }

    /**
     * @depends test_init
     */
    public function test_cron_job_instances()
    {
        $plugin = new ilScheduledEventsPlugin();

        $returned = [];
        foreach ($plugin->getCronJobInstances() as $job) {
            $this->assertInstanceOf(\ilCronJob::class, $job);
            $returned[] = $job->getId();
        }
        $this->assertEquals($returned, [\ilScheduledEventsJob::ID]);
    }

    /**
     * @depends test_init
     */
    public function test_txt_closure()
    {
        $plugin = new ilScheduledEventsPlugin();
        $this->assertInstanceOf(\Closure::class, $plugin->txtClosure());
    }
}

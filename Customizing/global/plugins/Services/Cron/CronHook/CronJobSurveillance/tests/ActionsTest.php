<?php
/**
 * Test ilActions
 */
use CaT\Plugins\CronJobSurveillance\Config;
use CaT\Plugins\CronJobSurveillance\Cron;
use CaT\Plugins\CronJobSurveillance\Mail;
use CaT\Plugins\CronJobSurveillance\ilActions;
use PHPUnit\Framework\TestCase;

class ActionsTest extends TestCase
{
    public function setUp() : void
    {
        $this->jobs = array(
            new Config\JobSetting('cron1'),
            new Config\JobSetting('cron2')
        );
        $jobs = $this->getMockBuilder(CaT\Plugins\CronJobSurveillance\Config\DB::class)
            ->setMethods(['select', 'create', 'selectForJob', 'deleteForJob', 'deleteAll'])
            ->getMock();
        $jobs
            ->method('select')
            ->willReturn($this->jobs);

        $this->mails = array(
            new Mail\MailSetting('some@mail.de'),
            new Mail\MailSetting('other@mail.org'),
        );
        $mails = $this->getMockBuilder(CaT\Plugins\CronJobSurveillance\Mail\DB::class)
            ->setMethods(['select', 'create', 'update', 'deleteFor'])
            ->getMock();
        $mails
            ->method('select')
            ->willReturn($this->mails);

        $this->actions = new ilActions($jobs, $mails);
    }

    public function testGetJobSettings()
    {
        $this->assertEquals($this->jobs, $this->actions->getJobSettings());
    }

    public function testGetMailSettings()
    {
        $this->assertEquals($this->mails, $this->actions->getMailSettings());
    }
}

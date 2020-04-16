<?php
/**
 * Test ilActions
 */
use CaT\Plugins\CronJobSurveillance\Cron;
use CaT\Plugins\CronJobSurveillance\Mail;
use PHPUnit\Framework\TestCase;

class MailerTest extends TestCase
{
    public function setUp() : void
    {
        $mailsettings = array(new Mail\MailSetting('some@mail.com'));
        $this->mailer = new Mail\Mailer($mailsettings, 'UnitTests');

        $this->job = $this->getMockBuilder(Cron\CronJob::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testMailSending()
    {
        $job_ok = clone $this->job;
        $job_fail = clone $this->job;

        $job_ok
            ->expects($this->once())
            ->method("getId")
            ->willReturn("OK");

        $job_fail
            ->expects($this->once())
            ->method("getId")
            ->willReturn("FAIL");

        $job_ok
            ->expects($this->once())
            ->method("getLastRunStart")
            ->willReturn(new DateTime('2018-05-26 17:45:10'));

        $job_fail
            ->expects($this->once())
            ->method("getLastRunStart")
            ->willReturn(new DateTime('2018-05-26 17:45:10'));


        $job_ok
            ->expects($this->once())
            ->method("getIsFinished")
            ->willReturn(true);

        $job_fail
            ->expects($this->once())
            ->method("getIsFinished")
            ->willReturn(false);

        $this->assertTrue($this->mailer->send(array($job_ok, $job_fail)));
    }
}

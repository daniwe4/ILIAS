<?php
/**
 * Test MailSetting class
 */
use CaT\Plugins\CronJobSurveillance\Mail;
use PHPUnit\Framework\TestCase;

class MailSettingsTest extends TestCase
{
    public function setUp() : void
    {
        $this->testmail = 'some@email.org';
        $this->setting = new Mail\MailSetting($this->testmail);
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(Mail\MailSetting::class, $this->setting);
    }

    public function testGetters()
    {
        $this->assertEquals($this->testmail, $this->setting->getRecipientAddress());
    }
}

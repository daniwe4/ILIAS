<?php
/**
 * Test StaticDB
 */
use CaT\Plugins\CronJobSurveillance\Mail;
use PHPUnit\Framework\TestCase;

class StaticDBTest extends TestCase
{
    public function setUp() : void
    {
        $this->mdb = new Mail\staticDB();
    }

    public function testMailSelect()
    {
        foreach ($this->mdb->select() as $mailsetting) {
            $this->assertInstanceOf(Mail\MailSetting::class, $mailsetting);
        }
    }

    public function testMailCreate()
    {
        try {
            $this->mdb->create(0, 'some@mail.com');
            $this->assertTrue(false);
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
    }

    public function testMailUpdate()
    {
        try {
            $this->mdb->update(new Mail\MailSetting('some@mail.com'));
            $this->assertTrue(false);
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
    }

    public function testMailDeleteFor()
    {
        try {
            $this->mdb->deleteFor('id');
            $this->assertTrue(false);
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
    }
}

<?php

use PHPUnit\Framework\TestCase;

use CaT\Plugins\CourseMailing\Settings\Setting;

class SettingTest extends TestCase
{
    public function initProvider()
    {
        return
        [[1,2,3,true]
        ,[1,2,0,false]
        ,[1,0,0,true]
        ,[0,0,0,false]
        ,[1,0,3,true]
        ,[0,2,0,false]
        ,[0,0,3,true]
        ,[0,2,3,false]];
    }

    /**
     * @dataProvider initProvider
     */
    public function test_init(
        $objid,
        $days_invitation,
        $days_invitation_reminder,
        $prevent_mailing
    ) {
        $setting = new Setting($objid, $days_invitation, $days_invitation_reminder, $prevent_mailing);
        $this->assertEquals($objid, $setting->getObjectId());
        $this->assertEquals($days_invitation, $setting->getDaysInvitation());
        $this->assertEquals($days_invitation_reminder, $setting->getDaysRemindInvitation());
        $this->assertEquals($prevent_mailing, $setting->getPreventMailing());
    }

    /**
     * @depends test_init
     */
    public function test_with_days_invitation()
    {
        $set = new Setting(1, 2, 3, true);
        $this->assertEquals($set->withDaysInvitation(2)->getDaysInvitation(), 2);
    }

    /**
     * @depends test_init
     */
    public function test_with_days_remind_invitation()
    {
        $set = new Setting(1, 2, 3, true);
        $this->assertEquals($set->withDaysRemindInvitation(3)->getDaysRemindInvitation(), 3);
    }

    /**
     * @depends test_init
     */
    public function test_with_remind_mailing()
    {
        $set = new Setting(1, 2, 3, true);
        $this->assertFalse($set->withPreventMailing(false)->getPreventMailing());
    }
}

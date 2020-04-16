<?php

use PHPUnit\Framework\TestCase;
use CaT\Plugins\Accomodation\ObjSettings\ObjSettings as Settings;
use CaT\Plugins\Accomodation\ObjSettings\Overnights as Overnights;

/**
 * Testing Overnights
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class OvernightsTest extends TestCase
{
    protected function getSettingsMock()
    {
        return $this->getMockBuilder(Settings::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
    protected function getOvernightsMock(array $with_methods)
    {
        return $this->getMockBuilder(Overnights::class)
            ->setMethods($with_methods)
            ->disableOriginalConstructor()
            ->getMock();
    }


    public function testEffectiveStartDateFromSettings()
    {
        $dat = new DateTime("2020-12-24");
        $mock = $this->getSettingsMock();

        $mock
            ->expects($this->once())
            ->method("getDatesByCourse")
            ->willReturn(false);
        $mock
            ->expects($this->once())
            ->method("getStartDate")
            ->willReturn($dat);

        $overnights = new Overnights($mock);
        $on_dat = $overnights->getEffectiveStartDate();
        $this->assertEquals($dat, $on_dat);
    }

    public function testEffectiveEndDateFromSettings()
    {
        $dat = new DateTime("2020-12-24");
        $mock = $this->getSettingsMock();

        $mock
            ->expects($this->once())
            ->method("getDatesByCourse")
            ->willReturn(false);

        $mock
            ->expects($this->once())
            ->method("getEndDate")
            ->willReturn($dat);

        $overnights = new Overnights($mock);
        $on_dat = $overnights->getEffectiveEndDate();
        $this->assertEquals($dat, $on_dat);
    }


    public function testEffectiveStartDateFromCourse()
    {
        $dat = new DateTime("2020-12-24");
        $mock = $this->getSettingsMock();

        $mock
            ->expects($this->once())
            ->method("getDatesByCourse")
            ->willReturn(true);

        $mock
            ->expects($this->never())
            ->method("getStartDate");

        $overnights = new Overnights($mock, $dat);
        $on_dat = $overnights->getEffectiveStartDate();
        $this->assertEquals($dat, $on_dat);
    }


    public function testEffectiveEndDateFromCourse()
    {
        $dat = new DateTime("2020-12-24");
        $mock = $this->getSettingsMock();

        $mock
            ->expects($this->once())
            ->method("getDatesByCourse")
            ->willReturn(true);

        $mock
            ->expects($this->never())
            ->method("getEndDate");

        $overnights = new Overnights($mock, null, $dat);
        $on_dat = $overnights->getEffectiveEndDate();
        $this->assertEquals($dat, $on_dat);
    }


    public function testOvernightsBaseFromSettings()
    {
        $start = new DateTime("2020-12-24");
        $end = new DateTime("2020-12-26");

        $mock = $this->getSettingsMock();
        $mock
            ->expects($this->exactly(2))
            ->method("getDatesByCourse")
            ->willReturn(false);
        $mock
            ->expects($this->once())
            ->method("getStartDate")
            ->willReturn($start);
        $mock
            ->expects($this->once())
            ->method("getEndDate")
            ->willReturn($end);

        $overnights = new Overnights($mock);

        $expected = [
            '2020-12-24', //24th to 25th
            '2020-12-25', //25th to 26th
        ];
        $this->assertEquals($expected, $overnights->getOvernightsBase());
    }


    public function testOvernightsBaseFromCourseDates()
    {
        $start = new DateTime("2020-12-24");
        $end = new DateTime("2020-12-26");

        $mock = $this->getSettingsMock();
        $mock
            ->expects($this->exactly(2))
            ->method("getDatesByCourse")
            ->willReturn(true);
        $mock
            ->expects($this->never())
            ->method("getStartDate");
        $mock
            ->expects($this->never())
            ->method("getEndDate");

        $overnights = new Overnights($mock, $start, $end);

        $expected = [
            '2020-12-24', //24th to 25th
            '2020-12-25', //25th to 26th
        ];
        $this->assertEquals($expected, $overnights->getOvernightsBase());
    }



    public function testOvernightsBaseCalculations()
    {
        $mock = $this->getSettingsMock();
        $mock
            ->method("getDatesByCourse")
            ->willReturn(true);

        $start = new DateTime("2020-12-31");
        $end = new DateTime("2021-01-2");
        $overnights = new Overnights($mock, $start, $end);
        $expected = [
            '2020-12-31',
            '2021-01-01',
        ];
        $this->assertEquals($expected, $overnights->getOvernightsBase());

        $start = new DateTime("2020-12-31");
        $end = new DateTime("2021-01-01");
        $overnights = new Overnights($mock, $start, $end);
        $expected = [
            '2020-12-31'
        ];
        $this->assertEquals($expected, $overnights->getOvernightsBase());

        $start = new DateTime("2020-12-31");
        $end = new DateTime("2020-12-31");
        $overnights = new Overnights($mock, $start, $end);
        $expected = [];
        $this->assertEquals($expected, $overnights->getOvernightsBase());
    }



    public function testOvernightsBase()
    {
        $start = new DateTime("2020-12-31");
        $end = new DateTime("2020-12-31");

        $overnights_mock = $this->getOvernightsMock([
            'getEffectiveStartDate',
            'getEffectiveEndDate'
        ]);
        $overnights_mock
            ->expects($this->once())
            ->method("getEffectiveStartDate")
            ->willReturn($start);
        $overnights_mock
            ->expects($this->once())
            ->method("getEffectiveEndDate")
            ->willReturn($end);

        $this->assertEquals(
            [],
            $overnights_mock->getOvernightsBase()
        );
    }

    public function testPriorNight()
    {
        $start = new DateTime("2020-12-31");
        $overnights_mock = $this->getOvernightsMock([
            'getEffectiveStartDate'
        ]);
        $overnights_mock
            ->expects($this->once())
            ->method("getEffectiveStartDate")
            ->willReturn($start);

        $this->assertEquals(
            '2020-12-30',
            $overnights_mock->getPriorNight()
        );
    }

    public function testPostNight()
    {
        $end = new DateTime("2020-12-30");
        $overnights_mock = $this->getOvernightsMock([
            'getEffectiveEndDate'
        ]);
        $overnights_mock
            ->expects($this->once())
            ->method("getEffectiveEndDate")
            ->willReturn($end);

        $this->assertEquals(
            '2020-12-30',
            $overnights_mock->getPostNight()
        );
    }

    public function testOvernightsExtended()
    {
        $overnights_mock = $this->getOvernightsMock([
            'getOvernightsBase',
            'getPriorNight',
            'getPostNight'
        ]);
        $overnights_mock
            ->expects($this->once())
            ->method("getOvernightsBase")
            ->willReturn(['2020-12-31']);
        $overnights_mock
            ->expects($this->once())
            ->method("getPriorNight")
            ->willReturn('2020-12-30');
        $overnights_mock
            ->expects($this->once())
            ->method("getPostNight")
            ->willReturn('2021-01-01');

        $this->assertEquals(
            ['2020-12-30', '2020-12-31', '2021-01-01'],
            $overnights_mock->getOvernightsExtended()
        );
    }

    public function testOvernightsExtendedCalculation()
    {
        $mock = $this->getSettingsMock();
        $mock
            ->method("getDatesByCourse")
            ->willReturn(true);

        $start = new DateTime("2020-12-24");
        $end = new DateTime("2020-12-26");
        $overnights = new Overnights($mock, $start, $end);

        $expected = [
            '2020-12-23',
            '2020-12-24',
            '2020-12-25',
            '2020-12-26',
        ];
        $this->assertEquals($expected, $overnights->getOvernightsExtended());
    }

    public function testOvernightsExtendedForOneDayCourse()
    {
        $mock = $this->getSettingsMock();
        $mock
            ->method("getDatesByCourse")
            ->willReturn(true);

        $start = new DateTime("2020-12-24");
        $end = new DateTime("2020-12-24");
        $overnights = new Overnights($mock, $start, $end);

        $expected = [
            '2020-12-23',
            '2020-12-24'
        ];
        $this->assertEquals($expected, $overnights->getOvernightsExtended());
    }

    public function testOvernightsForUser()
    {
        $settings = $this->getSettingsMock();
        $settings
            ->method("getDatesByCourse")
            ->willReturn(true);

        $settings
            ->expects($this->once())
            ->method("isPriorDayAllowed")
            ->willReturn(true);
        $settings
            ->expects($this->once())
            ->method("isFollowingDayAllowed")
            ->willReturn(true);

        $overnights = $this->getMockBuilder(Overnights::class)
            ->setMethods([
                'getOvernightsBase',
                'getPriorNight',
                'getPostNight'
            ])
            ->setConstructorArgs(array($settings))
            ->getMock();

        $overnights
            ->expects($this->once())
            ->method("getOvernightsBase")
            ->willReturn([]);

        $overnights
            ->expects($this->once())
            ->method("getPriorNight");

        $overnights
            ->expects($this->once())
            ->method("getPostNight");

        $overnights->getOvernightsForUser();
    }

    public function testBookingDeadline()
    {
        $start = new DateTime("2020-12-30");

        $settings = $this->getSettingsMock();
        $settings
            ->expects($this->once())
            ->method("getBookingEnd")
            ->willReturn(5);

        $overnights_mock = $this->getMockBuilder(Overnights::class)
            ->setMethods(['getEffectiveStartDate'])
            ->setConstructorArgs([$settings])
            ->getMock();

        $overnights_mock
            ->expects($this->once())
            ->method("getEffectiveStartDate")
            ->willReturn($start);

        $this->assertEquals(
            new \DateTime('2020-12-25'),
            $overnights_mock->getBookingDeadline()
        );
    }
}

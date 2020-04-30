<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

use ILIAS\TMS\Mailing\EluceoICalBuilder;
use ILIAS\TMS\CourseInfoImpl;
use CaT\Ente\Entity;
use PHPUnit\Framework\TestCase;

class EluceoICalBuilderTester extends EluceoICalBuilder
{
    public function _getEventUID($ref)
    {
        return $this->getEventUID($ref);
    }
}

class EluceoICalBuilderTest extends TestCase
{
    const CLIENT_ID = "client_id";
    const ORGANIZER_EMAIL = "noreply@cat06.de";
    const ORGANIZER_NAME = "CaT TMS";

    public function setUp() : void
    {
        $this->infos = [
            new CourseInfoImpl($this->createMock(Entity::class), "Titel", "Titel", "", 0, []),
            new CourseInfoImpl($this->createMock(Entity::class), "Datum", ["start" => "1969-07-20 20:17:40", "end" => "1969-07-20 20:17:40"], "", 0, []),
        ];
        $this->builder = new EluceoICalBuilderTester(self::CLIENT_ID, self::ORGANIZER_NAME, self::ORGANIZER_EMAIL);
    }

    public function testSmoke()
    {
        $this->assertIsString($this->builder->getIcalString("ref", $this->infos));
    }

    public function testFileContentIsString()
    {
        $string = $this->builder->getIcalString("ref", $this->infos);
        $file = $this->builder->saveICal("ref", $this->infos, "my.ical");
        $this->assertEquals($string, file_get_contents($file));
    }

    public function testGetEventUID()
    {
        $ref = "ref";
        $expected = self::CLIENT_ID . "-" . $ref;
        $this->assertEquals($expected, $this->builder->_getEventUID($ref));
    }

    public function testFileContainsOrganizer()
    {
        $string = $this->builder->getIcalString("ref", $this->infos);
        $this->assertRegexp("/ORGANIZER;CN=" . self::ORGANIZER_NAME . ":mailto:" . self::ORGANIZER_EMAIL . "/", $string);
    }
}

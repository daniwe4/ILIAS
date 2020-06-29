<?php
/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilSetupConfigTest extends TestCase
{
    public function testCreate() : void
    {
        $pw = new ILIAS\Data\Password('master_pass');
        $tz = new DateTimeZone('Europe/Berlin');

        $obj = new ilSetupConfig(
            'id',
            $pw,
            $tz,
            false
        );

        $this->assertEquals('id', $obj->getClientId());
        $this->assertEquals($pw, $obj->getMasterPassword());
        $this->assertEquals($tz, $obj->getServerTimeZone());
        $this->assertFalse($obj->getRegisterNIC());
    }

    public function testFailCreate() : void
    {
        $wrong_id = "foo_bar";
        $pw = new ILIAS\Data\Password('master_pass');
        $tz = new DateTimeZone('Europe/Berlin');

        $this->expectException(InvalidArgumentException::class);

        new ilSetupConfig(
            $wrong_id,
            $pw,
            $tz,
            false
        );
    }
}
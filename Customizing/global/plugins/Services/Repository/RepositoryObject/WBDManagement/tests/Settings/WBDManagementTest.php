<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WBDManagement\Settings;

use PHPUnit\Framework\TestCase;

class WBDManagementTest extends TestCase
{
    const OBJ_ID = 333;
    const ONLINE = true;
    const DOCUMENT_PATH = "/test/path/to/file.txt";
    const EMAIL = "test@test.de";


    public function test_create_empty() : WBDManagement
    {
        $wbd_management = new WBDManagement(self::OBJ_ID);

        $this->assertEquals(self::OBJ_ID, $wbd_management->getObjId());
        $this->assertFalse($wbd_management->isOnline());
        $this->assertNull($wbd_management->getDocumentPath());
        $this->assertNull($wbd_management->getEmail());

        return $wbd_management;
    }

    public function test_create_full()
    {
        $wbd_management = new WBDManagement(
            self::OBJ_ID,
            self::ONLINE,
            self::DOCUMENT_PATH,
            self::EMAIL
        );

        $this->assertEquals(self::OBJ_ID, $wbd_management->getObjId());
        $this->assertTrue($wbd_management->isOnline());
        $this->assertEquals(self::DOCUMENT_PATH, $wbd_management->getDocumentPath());
        $this->assertEquals(self::EMAIL, $wbd_management->getEmail());

        return $wbd_management;
    }

    /**
     * @depends test_create_empty
     */
    public function test_withOnline(WBDManagement $wbd_management)
    {
        $new_wbd_management = $wbd_management->withOnline(self::ONLINE);

        $this->assertEquals(self::OBJ_ID, $wbd_management->getObjId());
        $this->assertFalse($wbd_management->isOnline());
        $this->assertNull($wbd_management->getDocumentPath());
        $this->assertNull($wbd_management->getEmail());

        $this->assertEquals(self::OBJ_ID, $new_wbd_management->getObjId());
        $this->assertTrue($new_wbd_management->isOnline());
        $this->assertNull($new_wbd_management->getDocumentPath());
        $this->assertNull($new_wbd_management->getEmail());
    }

    /**
     * @depends test_create_empty
     */
    public function test_withDocumentPath(WBDManagement $wbd_management)
    {
        $new_wbd_management = $wbd_management->withDocumentPath(self::DOCUMENT_PATH);

        $this->assertEquals(self::OBJ_ID, $wbd_management->getObjId());
        $this->assertFalse($wbd_management->isOnline());
        $this->assertNull($wbd_management->getDocumentPath());
        $this->assertNull($wbd_management->getEmail());

        $this->assertEquals(self::OBJ_ID, $new_wbd_management->getObjId());
        $this->assertFalse($new_wbd_management->isOnline());
        $this->assertEquals(self::DOCUMENT_PATH, $new_wbd_management->getDocumentPath());
        $this->assertNull($new_wbd_management->getEmail());
    }

    /**
     * @depends test_create_empty
     */
    public function test_withEmail(WBDManagement $wbd_management)
    {
        $new_wbd_management = $wbd_management->withEmail(self::EMAIL);

        $this->assertEquals(self::OBJ_ID, $wbd_management->getObjId());
        $this->assertFalse($wbd_management->isOnline());
        $this->assertNull($wbd_management->getDocumentPath());
        $this->assertNull($wbd_management->getEmail());

        $this->assertEquals(self::OBJ_ID, $new_wbd_management->getObjId());
        $this->assertFalse($new_wbd_management->isOnline());
        $this->assertNull($new_wbd_management->getDocumentPath());
        $this->assertEquals(self::EMAIL, $new_wbd_management->getEmail());
    }
}

<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use PHPUnit\Framework\TestCase;

class CopySettingsDigesterTest extends TestCase
{
    const CREATE_COPY_SETTINGS = 'createCopySettings';
    const UPDATE_COPY_SETTINGS = 'updateCopySettings';
    const DELETE_COPY_SETTINGS = 'deleteCopySettings';

    private static $types = [
        self::CREATE_COPY_SETTINGS,
        self::UPDATE_COPY_SETTINGS,
        self::DELETE_COPY_SETTINGS
    ];

    /**
     * @var Mocks
     */
    protected $mock;

    public function setUp() : void
    {
        $this->mock = new Mocks();
    }

    public function testDigest() : void
    {
        $crs = $this->mock->getCrsMock();

        $obj = new CopySettingsDigester();

        foreach (self::$types as $type) {
            $payload = [
                'parent' => $crs,
                'type' => $type,
                'ref_id' => 30
            ];

            $result = $obj->digest($payload);

            switch ($type) {
                case self::CREATE_COPY_SETTINGS:
                case self::UPDATE_COPY_SETTINGS:
                    $this->assertTrue($result['is_template']);
                    $this->assertEquals(22, $result['crs_id']);
                    break;
                case self::DELETE_COPY_SETTINGS:
                    $this->assertTrue($result['is_template']);
                    $this->assertEquals(22, $result['crs_id']);

                    $payload['ref_id'] = 10;
                    $result = $obj->digest($payload);
                    $this->assertFalse($result['is_template']);
                    $this->assertEquals(22, $result['crs_id']);
                    break;
            }
        }
    }
}

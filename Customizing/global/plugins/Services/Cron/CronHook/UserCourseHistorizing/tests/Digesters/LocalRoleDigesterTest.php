<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use PHPUnit\Framework\TestCase;

class LocalRoleDigesterTestObject extends LocalRoleDigester
{
    protected function getAllAssignedRoles(int $crs_id, int $usr_id)
    {
        return ['r_title' => $crs_id . '_' . $usr_id];
    }
}

class LocalRoleDigesterTest extends TestCase
{
    const E_ASSIGN_USER = 'assignUser';
    const E_HISTORIZE_LOCAL_ROLES = 'historizeLocalRoles';
    const E_DEASSIGN_USER = 'deassignUser';

    private static $events = [
        self::E_ASSIGN_USER,
        self::E_HISTORIZE_LOCAL_ROLES,
        self::E_DEASSIGN_USER
    ];

    public function testDigest() : void
    {
        $payload = [
            'usr_id' => 44,
            'obj_id' => 22
        ];

        foreach (self::$events as $event) {
            $obj = new LocalRoleDigesterTestObject($event);

            $result = $obj->digest($payload);

            switch ($event) {
                case self::E_ASSIGN_USER:
                case self::E_HISTORIZE_LOCAL_ROLES:
                    $this->assertEquals('22_44', $result['roles']['r_title']);
                    break;
                case self::E_DEASSIGN_USER:
                    $this->assertTrue(count($result['roles']) === 0);
                    break;
            }
        }
    }
}

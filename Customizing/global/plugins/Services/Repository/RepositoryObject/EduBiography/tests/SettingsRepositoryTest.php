<?php

use CaT\Plugins\EduBiography\Settings as Settings;
use PHPUnit\Framework\TestCase;

class SettingsRepositoryTest extends TestCase
{
    public function test_init()
    {
        $db = $this->createMock(\ilDBInterface::class);
        $settings = new Settings\SettingsRepository($db);
        $this->assertInstanceOf(Settings\SettingsRepository::class, $settings);
    }

    public function test_create()
    {
        $db = $this->createMock(\ilDBInterface::class);

        $values = [
            'id' => [
                'integer',
                2
            ],
            'is_online' => [
                'integer',
                0
            ],
            'has_sup_overview' => [
                'integer',
                0
            ],
            'init_visible_columns' => [
                'text',
                null
            ],
            'recommendation_allowed' => [
                'integer',
                0
            ]
        ];
        $db->expects($this->once())
            ->method("insert")
            ->with("xebr_settings", $values)
        ;

        $s_r = new Settings\SettingsRepository($db);

        $settings = $s_r->createSettings(2);
        $this->assertEquals(2, $settings->id());
        $this->assertFalse($settings->isOnline());
        $this->assertFalse($settings->hasSuperiorOverview());
        $this->assertEquals([], $settings->getInitVisibleColumns());
        $this->assertEquals([], $settings->getInvisibleCourseTopics());
        $this->assertFalse($settings->getRecommendationAllowed());
    }

    public function test_update()
    {
        $db = $this->createMock(\ilDBInterface::class);

        $where = [
            "id" => [
                'integer',
                3
            ],
        ];
        $values = [
            "is_online" => [
                'integer',
                true
            ],
            "has_sup_overview" => [
                'integer',
                true
            ],
            "init_visible_columns" => [
                'text',
                serialize([])
            ],
            "invisible_crs_topics" => [
                'text',
                serialize([])
            ],
            "recommendation_allowed" => [
                'integer',
                0
            ]
        ];
        $db->expects($this->once())
            ->method("update")
            ->with("xebr_settings", $values, $where)
        ;

        $s_r = new Settings\SettingsRepository($db);
        $settings = new Settings\Settings(3, true, true, [], []);
        $s_r->updateSettings($settings);
        $this->assertEquals(3, $settings->id());
        $this->assertTrue($settings->isOnline());
        $this->assertTrue($settings->hasSuperiorOverview());
        $this->assertEquals([], $settings->getInitVisibleColumns());
        $this->assertEquals([], $settings->getInvisibleCourseTopics());
        $this->assertFalse($settings->getRecommendationAllowed());
    }

    public function test_delete()
    {
        $db = $this->createMock(\ilDBInterface::class);

        $q = 'DELETE FROM xebr_settings'
            . '	WHERE id = 4';
        $db->expects($this->once())
            ->method("manipulate")
            ->with($q)
        ;
        $db->expects($this->once())
            ->method("quote")
            ->with(4, 'integer')
            ->willReturn(4)
        ;
        $s_r = new Settings\SettingsRepository($db);
        $s = new Settings\Settings(4, true, true, [], []);
        $s_r->deleteSettings($s);
    }

    public function test_load()
    {
        $db = $this->createMock(\ilDBInterface::class);

        $q1 = 'SELECT is_online, has_sup_overview, init_visible_columns' . PHP_EOL
            . ',invisible_crs_topics' . PHP_EOL . ', recommendation_allowed '
            . 'FROM xebr_settings' . PHP_EOL
            . 'WHERE id = 5';
        $q2 = 'SELECT is_online, has_sup_overview, init_visible_columns' . PHP_EOL
            . ',invisible_crs_topics' . PHP_EOL . ', recommendation_allowed '
            . 'FROM xebr_settings' . PHP_EOL
            . 'WHERE id = 6';
        $db->expects($this->atLeastOnce())
            ->method("quote")
            ->withConsecutive([5, 'integer'], [6, 'integer'])
            ->willReturnOnConsecutiveCalls(5, 6)
        ;

        $db_result1 = [
            "is_online" => true,
            "has_sup_overview" => false,
            "init_visible_columns" => "a:0:{}",
            "invisible_crs_topics" => "a:0:{}",
            "recommendation_allowed" => true
        ];
        $db_result2 = [
            "is_online" => false,
            "has_sup_overview" => true,
            "init_visible_columns" => "a:0:{}",
            "invisible_crs_topics" => "a:0:{}",
            "recommendation_allowed" => true
        ];
        $db->expects($this->atLeastOnce())
            ->method("query")
            ->withConsecutive([$q1], [$q2])
            ->willReturnOnConsecutiveCalls($db_result1, $db_result2)
        ;

        $db->expects($this->atLeastOnce())
            ->method("fetchAssoc")
            ->withConsecutive([$db_result1], [$db_result2])
            ->willReturnOnConsecutiveCalls($db_result1, $db_result2)
        ;

        $s_r = new Settings\SettingsRepository($db);
        $set = $s_r->loadSettings(5);
        $this->assertEquals($set->id(), 5);
        $this->assertTrue($set->isOnline());
        $this->assertFalse($set->hasSuperiorOverview());

        $s_r = new Settings\SettingsRepository($db);
        $set = $s_r->loadSettings(6);
        $this->assertEquals($set->id(), 6);
        $this->assertFalse($set->isOnline());
        $this->assertTrue($set->hasSuperiorOverview());
    }
}

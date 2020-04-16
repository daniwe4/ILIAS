<?php

namespace CaT\Plugins\TrainingStatistics\Settings;

use PHPUnit\Framework\TestCase;

/**
 * Sample for PHP Unit tests
 */
class SettingsTest extends TestCase
{
    public function test_init()
    {
        $s = new Settings(1);
        $this->assertEquals($s->objId(), 1);
        $this->assertEquals($s->aggregateId(), Settings::AGGREGATE_ID_NONE);
        $this->assertEquals($s->online(), false);
        $this->assertEquals($s->global(), false);


        $s = new Settings(1, 'something', true, true);
        $this->assertEquals($s->objId(), 1);
        $this->assertEquals($s->aggregateId(), 'something');
        $this->assertEquals($s->online(), true);
        $this->assertEquals($s->global(), true);
    }

    public function test_with_aggregate_id()
    {
        $s = new Settings(1);
        $this->assertEquals(
            $s->withAggregateId('something_else')->aggregateId(),
            'something_else'
        );
    }

    public function test_with_online()
    {
        $s = new Settings(2);
        $this->assertEquals($s->withOnline(true)->online(), true);
        $this->assertEquals($s->withOnline(false)->online(), false);
    }

    public function test_with_global()
    {
        $s = new Settings(2);
        $this->assertEquals($s->withGlobal(true)->global(), true);
        $this->assertEquals($s->withGlobal(false)->global(), false);
    }
}

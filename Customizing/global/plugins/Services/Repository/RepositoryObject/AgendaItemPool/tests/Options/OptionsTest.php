<?php

namespace CaT\Plugins\AgendaItemPool\Options;

use PHPUnit\Framework\TestCase;

class OptionsTest extends TestCase
{
    public function test_getProperties()
    {
        $option = new Option(10, 333);

        $this->assertEquals(10, $option->getAgendaItemId());
        $this->assertEquals(333, $option->getCaptionId());
    }
}

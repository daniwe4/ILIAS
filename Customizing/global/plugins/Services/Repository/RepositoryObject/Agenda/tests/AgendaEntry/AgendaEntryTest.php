<?php

use PHPUnit\Framework\TestCase;
use CaT\Plugins\Agenda\AgendaEntry\AgendaEntry;

class AgendaEntryTest extends TestCase
{
    public function test_create()
    {
        $agenda_item = new AgendaEntry(1, 2, 10, 20, 50, 2.15, false, "content", "goals");
        $this->assertEquals(1, $agenda_item->getId());
        $this->assertEquals(2, $agenda_item->getObjId());
        $this->assertEquals(10, $agenda_item->getPoolItemId());
        $this->assertSame(20, $agenda_item->getDuration());
        $this->assertSame(50, $agenda_item->getPosition());
        $this->assertEquals(2.15, $agenda_item->getIDDTime());
        $this->assertFalse($agenda_item->getIsBlank());
        $this->assertEquals("content", $agenda_item->getAgendaItemContent());
        $this->assertEquals("goals", $agenda_item->getGoals());

        $agenda_item = new AgendaEntry(1, 2);
        $this->assertEquals(1, $agenda_item->getId());
        $this->assertEquals(2, $agenda_item->getObjId());
        $this->assertNull($agenda_item->getPoolItemId());
        $this->assertNull($agenda_item->getDuration());
        $this->assertEquals(0, $agenda_item->getPosition());
        $this->assertEquals(0.0, $agenda_item->getIDDTime());
        $this->assertFalse($agenda_item->getIsBlank());
        $this->assertNull($agenda_item->getAgendaItemContent());
        $this->assertNull($agenda_item->getGoals());

        return $agenda_item;
    }

    /**
     * @depends test_create
     */
    public function test_withPoolItemId(AgendaEntry $agenda_item)
    {
        $n_agenda_item = $agenda_item->withPoolItemId(10);

        $this->assertNotSame($agenda_item, $n_agenda_item);

        $this->assertEquals(1, $agenda_item->getId());
        $this->assertEquals(2, $agenda_item->getObjId());
        $this->assertNull($agenda_item->getPoolItemId());
        $this->assertNull($agenda_item->getDuration());
        $this->assertEquals(0, $agenda_item->getPosition());
        $this->assertEquals(0.0, $agenda_item->getIDDTime());
        $this->assertFalse($agenda_item->getIsBlank());
        $this->assertNull($agenda_item->getAgendaItemContent());
        $this->assertNull($agenda_item->getGoals());

        $this->assertEquals(1, $n_agenda_item->getId());
        $this->assertEquals(2, $n_agenda_item->getObjId());
        $this->assertEquals(10, $n_agenda_item->getPoolItemId());
        $this->assertNull($n_agenda_item->getDuration());
        $this->assertEquals(0, $n_agenda_item->getPosition());
        $this->assertEquals(0.0, $n_agenda_item->getIDDTime());
        $this->assertFalse($n_agenda_item->getIsBlank());
        $this->assertNull($n_agenda_item->getAgendaItemContent());
        $this->assertNull($n_agenda_item->getGoals());
    }

    /**
     * @depends test_create
     */
    public function test_withDuration(AgendaEntry $agenda_item)
    {
        $n_agenda_item = $agenda_item->withDuration(111);

        $this->assertNotSame($agenda_item, $n_agenda_item);

        $this->assertEquals(1, $agenda_item->getId());
        $this->assertEquals(2, $agenda_item->getObjId());
        $this->assertNull($agenda_item->getPoolItemId());
        $this->assertNull($agenda_item->getDuration());
        $this->assertEquals(0, $agenda_item->getPosition());
        $this->assertEquals(0.0, $agenda_item->getIDDTime());
        $this->assertFalse($agenda_item->getIsBlank());
        $this->assertNull($agenda_item->getAgendaItemContent());
        $this->assertNull($agenda_item->getGoals());

        $this->assertEquals(1, $n_agenda_item->getId());
        $this->assertEquals(2, $n_agenda_item->getObjId());
        $this->assertNull($n_agenda_item->getPoolItemId());
        $this->assertSame(111, $n_agenda_item->getDuration());
        $this->assertEquals(0, $n_agenda_item->getPosition());
        $this->assertEquals(0.0, $n_agenda_item->getIDDTime());
        $this->assertFalse($n_agenda_item->getIsBlank());
        $this->assertNull($n_agenda_item->getAgendaItemContent());
        $this->assertNull($n_agenda_item->getGoals());
    }

    /**
     * @depends test_create
     */
    public function test_withPosition(AgendaEntry $agenda_item)
    {
        $n_agenda_item = $agenda_item->withPosition(22);

        $this->assertNotSame($agenda_item, $n_agenda_item);

        $this->assertEquals(1, $agenda_item->getId());
        $this->assertEquals(2, $agenda_item->getObjId());
        $this->assertNull($agenda_item->getPoolItemId());
        $this->assertNull($agenda_item->getDuration());
        $this->assertEquals(0, $agenda_item->getPosition());
        $this->assertEquals(0.0, $agenda_item->getIDDTime());
        $this->assertFalse($agenda_item->getIsBlank());
        $this->assertNull($agenda_item->getAgendaItemContent());
        $this->assertNull($agenda_item->getGoals());

        $this->assertEquals(1, $n_agenda_item->getId());
        $this->assertEquals(2, $n_agenda_item->getObjId());
        $this->assertNull($n_agenda_item->getPoolItemId());
        $this->assertNull($n_agenda_item->getDuration());
        $this->assertEquals(22, $n_agenda_item->getPosition());
        $this->assertEquals(0.0, $n_agenda_item->getIDDTime());
        $this->assertFalse($n_agenda_item->getIsBlank());
        $this->assertNull($n_agenda_item->getAgendaItemContent());
        $this->assertNull($n_agenda_item->getGoals());
    }

    /**
     * @depends test_create
     */
    public function test_withIDDTime(AgendaEntry $agenda_item)
    {
        $n_agenda_item = $agenda_item->withIDDTime(3.33);

        $this->assertNotSame($agenda_item, $n_agenda_item);

        $this->assertEquals(1, $agenda_item->getId());
        $this->assertEquals(2, $agenda_item->getObjId());
        $this->assertNull($agenda_item->getPoolItemId());
        $this->assertNull($agenda_item->getDuration());
        $this->assertEquals(0, $agenda_item->getPosition());
        $this->assertEquals(0.0, $agenda_item->getIDDTime());
        $this->assertFalse($agenda_item->getIsBlank());
        $this->assertNull($agenda_item->getAgendaItemContent());
        $this->assertNull($agenda_item->getGoals());

        $this->assertEquals(1, $n_agenda_item->getId());
        $this->assertEquals(2, $n_agenda_item->getObjId());
        $this->assertNull($n_agenda_item->getPoolItemId());
        $this->assertNull($n_agenda_item->getDuration());
        $this->assertEquals(0, $n_agenda_item->getPosition());
        $this->assertEquals(3.33, $n_agenda_item->getIDDTime());
        $this->assertFalse($n_agenda_item->getIsBlank());
        $this->assertNull($n_agenda_item->getAgendaItemContent());
        $this->assertNull($n_agenda_item->getGoals());
    }

    /**
     * @depends test_create
     */
    public function test_withIsBlank(AgendaEntry $agenda_item)
    {
        $n_agenda_item = $agenda_item->withIsBlank(true);

        $this->assertNotSame($agenda_item, $n_agenda_item);

        $this->assertEquals(1, $agenda_item->getId());
        $this->assertEquals(2, $agenda_item->getObjId());
        $this->assertNull($agenda_item->getPoolItemId());
        $this->assertNull($agenda_item->getDuration());
        $this->assertEquals(0, $agenda_item->getPosition());
        $this->assertEquals(0.0, $agenda_item->getIDDTime());
        $this->assertFalse($agenda_item->getIsBlank());
        $this->assertNull($agenda_item->getAgendaItemContent());
        $this->assertNull($agenda_item->getGoals());

        $this->assertEquals(1, $n_agenda_item->getId());
        $this->assertEquals(2, $n_agenda_item->getObjId());
        $this->assertNull($n_agenda_item->getPoolItemId());
        $this->assertNull($n_agenda_item->getDuration());
        $this->assertEquals(0, $n_agenda_item->getPosition());
        $this->assertEquals(0.0, $n_agenda_item->getIDDTime());
        $this->assertTrue($n_agenda_item->getIsBlank());
        $this->assertNull($n_agenda_item->getAgendaItemContent());
        $this->assertNull($n_agenda_item->getGoals());
    }

    /**
     * @depends test_create
     */
    public function test_withAgendaItemContent(AgendaEntry $agenda_item)
    {
        $n_agenda_item = $agenda_item->withAgendaItemContent("bla");

        $this->assertNotSame($agenda_item, $n_agenda_item);

        $this->assertEquals(1, $agenda_item->getId());
        $this->assertEquals(2, $agenda_item->getObjId());
        $this->assertNull($agenda_item->getPoolItemId());
        $this->assertNull($agenda_item->getDuration());
        $this->assertEquals(0, $agenda_item->getPosition());
        $this->assertEquals(0.0, $agenda_item->getIDDTime());
        $this->assertFalse($agenda_item->getIsBlank());
        $this->assertNull($agenda_item->getAgendaItemContent());
        $this->assertNull($agenda_item->getGoals());

        $this->assertEquals(1, $n_agenda_item->getId());
        $this->assertEquals(2, $n_agenda_item->getObjId());
        $this->assertNull($n_agenda_item->getPoolItemId());
        $this->assertNull($n_agenda_item->getDuration());
        $this->assertEquals(0, $n_agenda_item->getPosition());
        $this->assertEquals(0.0, $n_agenda_item->getIDDTime());
        $this->assertFalse($n_agenda_item->getIsBlank());
        $this->assertEquals("bla", $n_agenda_item->getAgendaItemContent());
        $this->assertNull($n_agenda_item->getGoals());
    }

    /**
     * @depends test_create
     */
    public function test_withGoals(AgendaEntry $agenda_item)
    {
        $n_agenda_item = $agenda_item->withGoals("bla");

        $this->assertNotSame($agenda_item, $n_agenda_item);

        $this->assertEquals(1, $agenda_item->getId());
        $this->assertEquals(2, $agenda_item->getObjId());
        $this->assertNull($agenda_item->getPoolItemId());
        $this->assertNull($agenda_item->getDuration());
        $this->assertEquals(0, $agenda_item->getPosition());
        $this->assertEquals(0.0, $agenda_item->getIDDTime());
        $this->assertFalse($agenda_item->getIsBlank());
        $this->assertNull($agenda_item->getAgendaItemContent());
        $this->assertNull($agenda_item->getGoals());

        $this->assertEquals(1, $n_agenda_item->getId());
        $this->assertEquals(2, $n_agenda_item->getObjId());
        $this->assertNull($n_agenda_item->getPoolItemId());
        $this->assertNull($n_agenda_item->getDuration());
        $this->assertEquals(0, $n_agenda_item->getPosition());
        $this->assertEquals(0.0, $n_agenda_item->getIDDTime());
        $this->assertFalse($n_agenda_item->getIsBlank());
        $this->assertNull($n_agenda_item->getAgendaItemContent());
        $this->assertEquals("bla", $n_agenda_item->getGoals());
    }
}

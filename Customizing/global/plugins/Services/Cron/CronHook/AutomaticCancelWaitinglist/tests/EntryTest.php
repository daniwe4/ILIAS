<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\AutomaticCancelWaitinglist;

use PHPUnit\Framework\TestCase;

class EntryTest extends TestCase
{
    public function test_create_instance()
    {
        $id = 1;
        $crs_ref_id = 10;
        $date = new \DateTime();
        $message = "Message";

        $entry = new Log\Entry(
            $id,
            $crs_ref_id,
            $date,
            $message
        );

        $this->assertInstanceOf(Log\Entry::class, $entry);
        $this->assertEquals($id, $entry->getId());
        $this->assertEquals($crs_ref_id, $entry->getCrsRefId());
        $this->assertEquals($date, $entry->getDate());
        $this->assertSame($date, $entry->getDate());
        $this->assertEquals($message, $entry->getMessage());
    }
}

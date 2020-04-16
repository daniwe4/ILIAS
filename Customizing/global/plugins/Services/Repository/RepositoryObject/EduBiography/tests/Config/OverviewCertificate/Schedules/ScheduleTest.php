<?php

declare(strict_types=1);

namespace CaT\Plugins\EduBiography\Config\OverviewCertificate\Schedules;

use PHPUnit\Framework\TestCase;

class SettingsRepositoryTest extends TestCase
{
    public function test_create_instance()
    {
        $obj = new Schedule(
            1,
            "title",
            new \DateTime(),
            new \DateTime(),
            10,
            false,
            false
        );
        $this->assertInstanceOf(Schedule::class, $obj);
    }

    public function test_create_getter()
    {
        $id = 10;
        $title = "Year 2000";
        $start = new \DateTime();
        $end = new \DateTime();
        $min_idd_value = 350;
        $active = true;
        $participations_document_active = true;

        $obj = new Schedule($id, $title, $start, $end, $min_idd_value, $active, $participations_document_active);
        $this->assertInstanceOf(Schedule::class, $obj);

        $this->assertEquals($id, $obj->getId());
        $this->assertEquals($title, $obj->getTitle());
        $this->assertSame($start, $obj->getStart());
        $this->assertSame($end, $obj->getEnd());
        $this->assertEquals($min_idd_value, $obj->getMinIddValue());
        $this->assertTrue($obj->isActive());
        $this->assertTrue($obj->isParticipationsDocumentActive());
    }
}

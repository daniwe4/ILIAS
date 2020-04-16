<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase as TestCase;
use CaT\Plugins\ParticipationsImport\Mappings\ParticipationStatusRelationMapping;
use CaT\Plugins\ParticipationsImport\Mappings\ParticipationStatusMapping;
use CaT\Plugins\ParticipationsImport\Mappings\Mapping;

class ParticipationStatusRelationMappingTest extends TestCase
{
    public function test_init()
    {
        $psm = new ParticipationStatusRelationMapping();
        $this->assertInstanceOf(ParticipationStatusMapping::class, $psm);
    }

    public function test_add_and_read_values()
    {
        $psm = new ParticipationStatusRelationMapping();
        $psm->addRelation('succ_1', 'successful');
        $psm->addRelation('succ_2', 'successful');
        $psm->addRelation('absent_1', 'absent');
        $psm->addRelation('in_prog', 'in_progress');

        $this->assertEquals($psm->ilParticipationStatusForExternStatus('succ_1'), 'successful');
        $this->assertEquals($psm->ilParticipationStatusForExternStatus('succ_2'), 'successful');
        $this->assertEquals($psm->ilParticipationStatusForExternStatus('absent_1'), 'absent');
        $this->assertEquals($psm->ilParticipationStatusForExternStatus('in_prog'), 'in_progress');
        $this->assertEquals($psm->ilParticipationStatusForExternStatus('foo'), Mapping::NO_MAPPING_FOUND_STRING);
    }

    /**
     * @expectException CaT\Plugins\ParticipationsImport\Mappings\RelationException
     */
    public function test_invalid_status()
    {
        $psm = new ParticipationStatusRelationMapping();

        try {
            $psm->addRelation('foo', 'bar');
            $this->assertTrue(false);
        } catch (CaT\Plugins\ParticipationsImport\Mappings\RelationException $e) {
            $this->assertTrue(true);
        }
    }
}

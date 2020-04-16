<?php

use PHPUnit\Framework\TestCase;
use CaT\Plugins\CopySettings\Children\Child;

/**
 * Testcase for immutable object child settings
 *
 * @author Stefan Hecken	<stefan.hecken@concspts-and-training.de>
 */
class CopySettingsForChildrenTest extends TestCase
{
    public function test_create()
    {
        $scs = new Child(1, 2, 3, false, Child::NOTHING);

        $this->assertEquals(1, $scs->getObjId());
        $this->assertEquals(2, $scs->getTargetRefId());
        $this->assertEquals(3, $scs->getTargetObjId());
        $this->assertFalse($scs->isReferenced());
        $this->assertEquals(Child::NOTHING, $scs->getProcessType());

        return $scs;
    }

    /**
     * @depends test_create
     */
    public function test_withTargetRefId($scs)
    {
        $n_scs = $scs->withTargetRefId(22);

        $this->assertEquals(1, $scs->getObjId());
        $this->assertEquals(2, $scs->getTargetRefId());
        $this->assertEquals(3, $scs->getTargetObjId());
        $this->assertFalse($scs->isReferenced());
        $this->assertEquals(Child::NOTHING, $scs->getProcessType());

        $this->assertEquals(1, $n_scs->getObjId());
        $this->assertEquals(22, $n_scs->getTargetRefId());
        $this->assertEquals(3, $n_scs->getTargetObjId());
        $this->assertFalse($n_scs->isReferenced());
        $this->assertEquals(Child::NOTHING, $n_scs->getProcessType());

        $this->assertNotSame($scs, $n_scs);

        return $n_scs;
    }

    /**
     * @depends test_withTargetRefId
     */
    public function test_withTargetObjId($scs)
    {
        $n_scs = $scs->withTargetObjId(12);

        $this->assertEquals(1, $scs->getObjId());
        $this->assertEquals(22, $scs->getTargetRefId());
        $this->assertEquals(3, $scs->getTargetObjId());
        $this->assertFalse($scs->isReferenced());
        $this->assertEquals(Child::NOTHING, $scs->getProcessType());

        $this->assertEquals(1, $n_scs->getObjId());
        $this->assertEquals(22, $n_scs->getTargetRefId());
        $this->assertEquals(12, $n_scs->getTargetObjId());
        $this->assertFalse($n_scs->isReferenced());
        $this->assertEquals(Child::NOTHING, $n_scs->getProcessType());

        $this->assertNotSame($scs, $n_scs);

        return $n_scs;
    }

    /**
     * @depends test_withTargetObjId
     */
    public function test_withIsReferenced($scs)
    {
        $n_scs = $scs->withIsReferenced(true);

        $this->assertEquals(1, $scs->getObjId());
        $this->assertEquals(22, $scs->getTargetRefId());
        $this->assertEquals(12, $n_scs->getTargetObjId());
        $this->assertFalse($scs->isReferenced());
        $this->assertEquals(Child::NOTHING, $scs->getProcessType());

        $this->assertEquals(1, $n_scs->getObjId());
        $this->assertEquals(22, $n_scs->getTargetRefId());
        $this->assertEquals(12, $n_scs->getTargetObjId());
        $this->assertTrue($n_scs->isReferenced());
        $this->assertEquals(Child::NOTHING, $n_scs->getProcessType());

        $this->assertNotSame($scs, $n_scs);

        return $n_scs;
    }

    /**
     * @depends test_withIsReferenced
     */
    public function test_withProcessTyp($scs)
    {
        $n_scs = $scs->withProcessType(Child::COPY);

        $this->assertEquals(1, $n_scs->getObjId());
        $this->assertEquals(22, $n_scs->getTargetRefId());
        $this->assertEquals(12, $n_scs->getTargetObjId());
        $this->assertTrue($n_scs->isReferenced());
        $this->assertEquals(Child::NOTHING, $scs->getProcessType());

        $this->assertEquals(1, $n_scs->getObjId());
        $this->assertEquals(22, $n_scs->getTargetRefId());
        $this->assertEquals(12, $n_scs->getTargetObjId());
        $this->assertTrue($n_scs->isReferenced());
        $this->assertEquals(Child::COPY, $n_scs->getProcessType());

        $this->assertNotSame($scs, $n_scs);

        return $n_scs;
    }
}

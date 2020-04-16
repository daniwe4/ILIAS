<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\EduTracking\Purposes\WBD;

use PHPUnit\Framework\TestCase;
use CaT\Plugins\EduTracking\Mocks;

class WBDDataInterfaceTest extends TestCase
{
    /**
     * @var Mocks
     */
    protected $mocks;

    /**
     * @var WBDDataInterface
     */
    protected $obj;

    public function setUp() : void
    {
        $this->mocks = new Mocks();
        $db_mock = $this->mocks->getIliasDBMock();
        $db = new ilDB($db_mock, $this->mocks->getIliasAppEventHandler());

        $wbd = new WBD($db, $this->mocks->getIliasAppEventHandler(), $this->mocks->getEduTrackingObjectMock());
        $configWBD = new Configuration\ConfigWBD(22, true, 'test_contact', 33);
        $wbdUserDataProvider = new IliasWBDUserDataProvider();
        $wbdObjectProvider = new IliasWBDObjectProvider($this->mocks->getIliasTree());

        $this->obj = new WBDDataInterface(
            $wbd,
            $configWBD,
            $wbdUserDataProvider,
            $wbdObjectProvider
        );
    }

    public function testCreate() : void
    {
        $this->assertInstanceOf(WBDDataInterface::class, $this->obj);
    }
}

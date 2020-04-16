<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\EduTracking\Purposes\WBD;

use PHPUnit\Framework\TestCase;
use CaT\Plugins\EduTracking\Mocks;
use CaT\Plugins\CourseClassification\Settings\CourseClassification;
use CaT\Plugins\CourseClassification\Settings\Contact;

require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/CourseClassification/classes/class.ilObjCourseClassification.php";

class WBDDataInterfaceTest extends TestCase
{
    private static $contacts = [
        Configuration\ConfigWBD::M_FIX_CONTACT,
        Configuration\ConfigWBD::M_COURSE_TUTOR,
        Configuration\ConfigWBD::M_COURSE_ADMIN
    ];

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
        $this->db = new ilDB($db_mock, $this->mocks->getIliasAppEventHandler());
    }

    public function testCreate() : void
    {
        $obj = $this->getWBDDataInterface('test_contact');
        $this->assertInstanceOf(WBDDataInterface::class, $obj);
    }

    public function testLoadUserInfosWithDifferentContacts() : void
    {
        $data = [
            'test_title',
            'test_firstname',
            'test_lastname',
            'test_phone',
            'test_mail'
        ];

        $udp = $this->createMock(WBDUserDataProvider::class);
        $udp
            ->expects($this->exactly(3))
            ->method('getUserInformation')
            ->with(33)
            ->willReturn($data)
        ;

        foreach (self::$contacts as $contact) {
            $obj = $this->getWBDDataInterface($contact, $udp);

            $this->assertEquals('test_title', $obj->getContactTitle());
            $this->assertEquals('test_firstname', $obj->getContactFirstname());
            $this->assertEquals('test_lastname', $obj->getContactLastname());
            $this->assertEquals('test_phone', $obj->getContactPhone());
            $this->assertEquals('test_mail', $obj->getContactEmail());
        }
    }

    public function testLoadContactFromXCCL() : void
    {
        $contact = $this->createMock(Contact::class);
        $contact
            ->expects($this->once())
            ->method('getName')
            ->willReturn('test_lastname');
        $contact
            ->expects($this->once())
            ->method('getPhone')
            ->willReturn('test_phone')
        ;
        $contact
            ->expects($this->once())
            ->method('getMail')
            ->willReturn('test_mail')
        ;

        $course_classification = $this->createMock(CourseClassification::class);
        $course_classification
            ->expects($this->once())
            ->method('getContact')
            ->willReturn($contact)
        ;

        $course_classification_obj = $this->createMock(\ilObjCourseClassification::class);
        $course_classification_obj
            ->expects($this->once())
            ->method('getCourseClassification')
            ->willReturn($course_classification)
        ;

        $op = $this->createMock(WBDObjectProvider::class);
        $op
            ->expects($this->once())
            ->method('getFirstChildOfByType')
            ->with(33, 'xccl')
            ->willReturn($course_classification_obj)
        ;

        $obj = $this->getWBDDataInterface(Configuration\ConfigWBD::M_XCCL_CONTACT, null, $op);

        $this->assertEquals('test_lastname', $obj->getContactLastname());
        $this->assertEquals('test_phone', $obj->getContactPhone());
        $this->assertEquals('test_mail', $obj->getContactEmail());
    }

    protected function getWBDDataInterface(
        string $contact,
        WBDUserDataProvider $udp = null,
        WBDObjectProvider $op = null
    ) : WBDDataInterface {
        $wbd = new WBD($this->db, $this->mocks->getIliasAppEventHandler(), $this->mocks->getEduTrackingObjectMock());
        $configWBD = new Configuration\ConfigWBD(22, true, $contact, 33);
        $wbdUserDataProvider = new IliasWBDUserDataProvider();
        $wbdObjectProvider = new IliasWBDObjectProvider($this->mocks->getIliasTree());

        if (!is_null($udp)) {
            $wbdUserDataProvider = $udp;
        }

        if (!is_null($op)) {
            $wbdObjectProvider = $op;
        }

        return new WBDDataInterface(
            $wbd,
            $configWBD,
            $wbdUserDataProvider,
            $wbdObjectProvider
        );
    }
}

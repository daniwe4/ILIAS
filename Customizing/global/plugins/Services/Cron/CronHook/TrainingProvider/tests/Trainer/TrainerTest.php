<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\TrainingProvider\Trainer;

use PHPUnit\Framework\TestCase;

class TrainerTest extends TestCase
{
    /**
     * @var Trainer
     */
    protected $obj;

    public function setUp() : void
    {
        $this->obj = new Trainer(
            22,
            'test_title',
            'test_salutation',
            'test_firstname',
            'test_lastname',
            33,
            'test_email',
            'test_phone',
            'test_mobile_number',
            0.2,
            'test_extra_infos',
            false
        );
    }

    public function testCreate() : void
    {
        $this->assertInstanceOf(Trainer::class, $this->obj);
        $this->assertEquals(22, $this->obj->getId());
        $this->assertEquals('test_title', $this->obj->getTitle());
        $this->assertEquals('test_salutation', $this->obj->getSalutation());
        $this->assertEquals('test_firstname', $this->obj->getFirstname());
        $this->assertEquals('test_lastname', $this->obj->getLastname());
        $this->assertEquals(33, $this->obj->getProviderId());
        $this->assertEquals('test_email', $this->obj->getEmail());
        $this->assertEquals('test_phone', $this->obj->getPhone());
        $this->assertEquals('test_mobile_number', $this->obj->getMobileNumber());
        $this->assertEquals(0.2, $this->obj->getFee());
        $this->assertEquals('test_extra_infos', $this->obj->getExtraInfos());
        $this->assertFalse($this->obj->getActive());
    }

    public function testWithTitle() : void
    {
        $new_obj = $this->obj->withTitle('test_new_string');

        $this->assertEquals(22, $this->obj->getId());
        $this->assertEquals('test_title', $this->obj->getTitle());
        $this->assertEquals('test_salutation', $this->obj->getSalutation());
        $this->assertEquals('test_firstname', $this->obj->getFirstname());
        $this->assertEquals('test_lastname', $this->obj->getLastname());
        $this->assertEquals(33, $this->obj->getProviderId());
        $this->assertEquals('test_email', $this->obj->getEmail());
        $this->assertEquals('test_phone', $this->obj->getPhone());
        $this->assertEquals('test_mobile_number', $this->obj->getMobileNumber());
        $this->assertEquals(0.2, $this->obj->getFee());
        $this->assertEquals('test_extra_infos', $this->obj->getExtraInfos());
        $this->assertFalse($this->obj->getActive());

        $this->assertEquals(22, $new_obj->getId());
        $this->assertEquals('test_new_string', $new_obj->getTitle());
        $this->assertEquals('test_salutation', $new_obj->getSalutation());
        $this->assertEquals('test_firstname', $new_obj->getFirstname());
        $this->assertEquals('test_lastname', $new_obj->getLastname());
        $this->assertEquals(33, $new_obj->getProviderId());
        $this->assertEquals('test_email', $new_obj->getEmail());
        $this->assertEquals('test_phone', $new_obj->getPhone());
        $this->assertEquals('test_mobile_number', $new_obj->getMobileNumber());
        $this->assertEquals(0.2, $new_obj->getFee());
        $this->assertEquals('test_extra_infos', $new_obj->getExtraInfos());
        $this->assertFalse($new_obj->getActive());
    }

    public function testWithSalutation() : void
    {
        $new_obj = $this->obj->withSalutation('test_new_string');

        $this->assertEquals(22, $this->obj->getId());
        $this->assertEquals('test_title', $this->obj->getTitle());
        $this->assertEquals('test_salutation', $this->obj->getSalutation());
        $this->assertEquals('test_firstname', $this->obj->getFirstname());
        $this->assertEquals('test_lastname', $this->obj->getLastname());
        $this->assertEquals(33, $this->obj->getProviderId());
        $this->assertEquals('test_email', $this->obj->getEmail());
        $this->assertEquals('test_phone', $this->obj->getPhone());
        $this->assertEquals('test_mobile_number', $this->obj->getMobileNumber());
        $this->assertEquals(0.2, $this->obj->getFee());
        $this->assertEquals('test_extra_infos', $this->obj->getExtraInfos());
        $this->assertFalse($this->obj->getActive());

        $this->assertEquals(22, $new_obj->getId());
        $this->assertEquals('test_title', $new_obj->getTitle());
        $this->assertEquals('test_new_string', $new_obj->getSalutation());
        $this->assertEquals('test_firstname', $new_obj->getFirstname());
        $this->assertEquals('test_lastname', $new_obj->getLastname());
        $this->assertEquals(33, $new_obj->getProviderId());
        $this->assertEquals('test_email', $new_obj->getEmail());
        $this->assertEquals('test_phone', $new_obj->getPhone());
        $this->assertEquals('test_mobile_number', $new_obj->getMobileNumber());
        $this->assertEquals(0.2, $new_obj->getFee());
        $this->assertEquals('test_extra_infos', $new_obj->getExtraInfos());
        $this->assertFalse($new_obj->getActive());
    }

    public function testWithFirstname() : void
    {
        $new_obj = $this->obj->withFirstname('test_new_string');

        $this->assertEquals(22, $this->obj->getId());
        $this->assertEquals('test_title', $this->obj->getTitle());
        $this->assertEquals('test_salutation', $this->obj->getSalutation());
        $this->assertEquals('test_firstname', $this->obj->getFirstname());
        $this->assertEquals('test_lastname', $this->obj->getLastname());
        $this->assertEquals(33, $this->obj->getProviderId());
        $this->assertEquals('test_email', $this->obj->getEmail());
        $this->assertEquals('test_phone', $this->obj->getPhone());
        $this->assertEquals('test_mobile_number', $this->obj->getMobileNumber());
        $this->assertEquals(0.2, $this->obj->getFee());
        $this->assertEquals('test_extra_infos', $this->obj->getExtraInfos());
        $this->assertFalse($this->obj->getActive());

        $this->assertEquals(22, $new_obj->getId());
        $this->assertEquals('test_title', $new_obj->getTitle());
        $this->assertEquals('test_salutation', $new_obj->getSalutation());
        $this->assertEquals('test_new_string', $new_obj->getFirstname());
        $this->assertEquals('test_lastname', $new_obj->getLastname());
        $this->assertEquals(33, $new_obj->getProviderId());
        $this->assertEquals('test_email', $new_obj->getEmail());
        $this->assertEquals('test_phone', $new_obj->getPhone());
        $this->assertEquals('test_mobile_number', $new_obj->getMobileNumber());
        $this->assertEquals(0.2, $new_obj->getFee());
        $this->assertEquals('test_extra_infos', $new_obj->getExtraInfos());
        $this->assertFalse($new_obj->getActive());
    }

    public function testWithLastname() : void
    {
        $new_obj = $this->obj->withLastname('test_new_string');

        $this->assertEquals(22, $this->obj->getId());
        $this->assertEquals('test_title', $this->obj->getTitle());
        $this->assertEquals('test_salutation', $this->obj->getSalutation());
        $this->assertEquals('test_firstname', $this->obj->getFirstname());
        $this->assertEquals('test_lastname', $this->obj->getLastname());
        $this->assertEquals(33, $this->obj->getProviderId());
        $this->assertEquals('test_email', $this->obj->getEmail());
        $this->assertEquals('test_phone', $this->obj->getPhone());
        $this->assertEquals('test_mobile_number', $this->obj->getMobileNumber());
        $this->assertEquals(0.2, $this->obj->getFee());
        $this->assertEquals('test_extra_infos', $this->obj->getExtraInfos());
        $this->assertFalse($this->obj->getActive());

        $this->assertEquals(22, $new_obj->getId());
        $this->assertEquals('test_title', $new_obj->getTitle());
        $this->assertEquals('test_salutation', $new_obj->getSalutation());
        $this->assertEquals('test_firstname', $new_obj->getFirstname());
        $this->assertEquals('test_new_string', $new_obj->getLastname());
        $this->assertEquals(33, $new_obj->getProviderId());
        $this->assertEquals('test_email', $new_obj->getEmail());
        $this->assertEquals('test_phone', $new_obj->getPhone());
        $this->assertEquals('test_mobile_number', $new_obj->getMobileNumber());
        $this->assertEquals(0.2, $new_obj->getFee());
        $this->assertEquals('test_extra_infos', $new_obj->getExtraInfos());
        $this->assertFalse($new_obj->getActive());
    }

    public function testWithProviderId() : void
    {
        $new_obj = $this->obj->withProviderId(55);

        $this->assertEquals(22, $this->obj->getId());
        $this->assertEquals('test_title', $this->obj->getTitle());
        $this->assertEquals('test_salutation', $this->obj->getSalutation());
        $this->assertEquals('test_firstname', $this->obj->getFirstname());
        $this->assertEquals('test_lastname', $this->obj->getLastname());
        $this->assertEquals(33, $this->obj->getProviderId());
        $this->assertEquals('test_email', $this->obj->getEmail());
        $this->assertEquals('test_phone', $this->obj->getPhone());
        $this->assertEquals('test_mobile_number', $this->obj->getMobileNumber());
        $this->assertEquals(0.2, $this->obj->getFee());
        $this->assertEquals('test_extra_infos', $this->obj->getExtraInfos());
        $this->assertFalse($this->obj->getActive());

        $this->assertEquals(22, $new_obj->getId());
        $this->assertEquals('test_title', $new_obj->getTitle());
        $this->assertEquals('test_salutation', $new_obj->getSalutation());
        $this->assertEquals('test_firstname', $new_obj->getFirstname());
        $this->assertEquals('test_lastname', $new_obj->getLastname());
        $this->assertEquals(55, $new_obj->getProviderId());
        $this->assertEquals('test_email', $new_obj->getEmail());
        $this->assertEquals('test_phone', $new_obj->getPhone());
        $this->assertEquals('test_mobile_number', $new_obj->getMobileNumber());
        $this->assertEquals(0.2, $new_obj->getFee());
        $this->assertEquals('test_extra_infos', $new_obj->getExtraInfos());
        $this->assertFalse($new_obj->getActive());
    }

    public function testWithEmail() : void
    {
        $new_obj = $this->obj->withEmail('test_new_string');

        $this->assertEquals(22, $this->obj->getId());
        $this->assertEquals('test_title', $this->obj->getTitle());
        $this->assertEquals('test_salutation', $this->obj->getSalutation());
        $this->assertEquals('test_firstname', $this->obj->getFirstname());
        $this->assertEquals('test_lastname', $this->obj->getLastname());
        $this->assertEquals(33, $this->obj->getProviderId());
        $this->assertEquals('test_email', $this->obj->getEmail());
        $this->assertEquals('test_phone', $this->obj->getPhone());
        $this->assertEquals('test_mobile_number', $this->obj->getMobileNumber());
        $this->assertEquals(0.2, $this->obj->getFee());
        $this->assertEquals('test_extra_infos', $this->obj->getExtraInfos());
        $this->assertFalse($this->obj->getActive());

        $this->assertEquals(22, $new_obj->getId());
        $this->assertEquals('test_title', $new_obj->getTitle());
        $this->assertEquals('test_salutation', $new_obj->getSalutation());
        $this->assertEquals('test_firstname', $new_obj->getFirstname());
        $this->assertEquals('test_lastname', $new_obj->getLastname());
        $this->assertEquals(33, $new_obj->getProviderId());
        $this->assertEquals('test_new_string', $new_obj->getEmail());
        $this->assertEquals('test_phone', $new_obj->getPhone());
        $this->assertEquals('test_mobile_number', $new_obj->getMobileNumber());
        $this->assertEquals(0.2, $new_obj->getFee());
        $this->assertEquals('test_extra_infos', $new_obj->getExtraInfos());
        $this->assertFalse($new_obj->getActive());
    }

    public function testWithPhone() : void
    {
        $new_obj = $this->obj->withPhone('test_new_string');

        $this->assertEquals(22, $this->obj->getId());
        $this->assertEquals('test_title', $this->obj->getTitle());
        $this->assertEquals('test_salutation', $this->obj->getSalutation());
        $this->assertEquals('test_firstname', $this->obj->getFirstname());
        $this->assertEquals('test_lastname', $this->obj->getLastname());
        $this->assertEquals(33, $this->obj->getProviderId());
        $this->assertEquals('test_email', $this->obj->getEmail());
        $this->assertEquals('test_phone', $this->obj->getPhone());
        $this->assertEquals('test_mobile_number', $this->obj->getMobileNumber());
        $this->assertEquals(0.2, $this->obj->getFee());
        $this->assertEquals('test_extra_infos', $this->obj->getExtraInfos());
        $this->assertFalse($this->obj->getActive());

        $this->assertEquals(22, $new_obj->getId());
        $this->assertEquals('test_title', $new_obj->getTitle());
        $this->assertEquals('test_salutation', $new_obj->getSalutation());
        $this->assertEquals('test_firstname', $new_obj->getFirstname());
        $this->assertEquals('test_lastname', $new_obj->getLastname());
        $this->assertEquals(33, $new_obj->getProviderId());
        $this->assertEquals('test_email', $new_obj->getEmail());
        $this->assertEquals('test_new_string', $new_obj->getPhone());
        $this->assertEquals('test_mobile_number', $new_obj->getMobileNumber());
        $this->assertEquals(0.2, $new_obj->getFee());
        $this->assertEquals('test_extra_infos', $new_obj->getExtraInfos());
        $this->assertFalse($new_obj->getActive());
    }

    public function testWithMobileNumber() : void
    {
        $new_obj = $this->obj->withMobileNumber('test_new_string');

        $this->assertEquals(22, $this->obj->getId());
        $this->assertEquals('test_title', $this->obj->getTitle());
        $this->assertEquals('test_salutation', $this->obj->getSalutation());
        $this->assertEquals('test_firstname', $this->obj->getFirstname());
        $this->assertEquals('test_lastname', $this->obj->getLastname());
        $this->assertEquals(33, $this->obj->getProviderId());
        $this->assertEquals('test_email', $this->obj->getEmail());
        $this->assertEquals('test_phone', $this->obj->getPhone());
        $this->assertEquals('test_mobile_number', $this->obj->getMobileNumber());
        $this->assertEquals(0.2, $this->obj->getFee());
        $this->assertEquals('test_extra_infos', $this->obj->getExtraInfos());
        $this->assertFalse($this->obj->getActive());

        $this->assertEquals(22, $new_obj->getId());
        $this->assertEquals('test_title', $new_obj->getTitle());
        $this->assertEquals('test_salutation', $new_obj->getSalutation());
        $this->assertEquals('test_firstname', $new_obj->getFirstname());
        $this->assertEquals('test_lastname', $new_obj->getLastname());
        $this->assertEquals(33, $new_obj->getProviderId());
        $this->assertEquals('test_email', $new_obj->getEmail());
        $this->assertEquals('test_phone', $new_obj->getPhone());
        $this->assertEquals('test_new_string', $new_obj->getMobileNumber());
        $this->assertEquals(0.2, $new_obj->getFee());
        $this->assertEquals('test_extra_infos', $new_obj->getExtraInfos());
        $this->assertFalse($new_obj->getActive());
    }

    public function testWithFee() : void
    {
        $new_obj = $this->obj->withFee(3.33);

        $this->assertEquals(22, $this->obj->getId());
        $this->assertEquals('test_title', $this->obj->getTitle());
        $this->assertEquals('test_salutation', $this->obj->getSalutation());
        $this->assertEquals('test_firstname', $this->obj->getFirstname());
        $this->assertEquals('test_lastname', $this->obj->getLastname());
        $this->assertEquals(33, $this->obj->getProviderId());
        $this->assertEquals('test_email', $this->obj->getEmail());
        $this->assertEquals('test_phone', $this->obj->getPhone());
        $this->assertEquals('test_mobile_number', $this->obj->getMobileNumber());
        $this->assertEquals(0.2, $this->obj->getFee());
        $this->assertEquals('test_extra_infos', $this->obj->getExtraInfos());
        $this->assertFalse($this->obj->getActive());

        $this->assertEquals(22, $new_obj->getId());
        $this->assertEquals('test_title', $new_obj->getTitle());
        $this->assertEquals('test_salutation', $new_obj->getSalutation());
        $this->assertEquals('test_firstname', $new_obj->getFirstname());
        $this->assertEquals('test_lastname', $new_obj->getLastname());
        $this->assertEquals(33, $new_obj->getProviderId());
        $this->assertEquals('test_email', $new_obj->getEmail());
        $this->assertEquals('test_phone', $new_obj->getPhone());
        $this->assertEquals('test_mobile_number', $new_obj->getMobileNumber());
        $this->assertEquals(3.33, $new_obj->getFee());
        $this->assertEquals('test_extra_infos', $new_obj->getExtraInfos());
        $this->assertFalse($new_obj->getActive());
    }

    public function testWithExtraInfos() : void
    {
        $new_obj = $this->obj->withExtraInfos('test_new_string');

        $this->assertEquals(22, $this->obj->getId());
        $this->assertEquals('test_title', $this->obj->getTitle());
        $this->assertEquals('test_salutation', $this->obj->getSalutation());
        $this->assertEquals('test_firstname', $this->obj->getFirstname());
        $this->assertEquals('test_lastname', $this->obj->getLastname());
        $this->assertEquals(33, $this->obj->getProviderId());
        $this->assertEquals('test_email', $this->obj->getEmail());
        $this->assertEquals('test_phone', $this->obj->getPhone());
        $this->assertEquals('test_mobile_number', $this->obj->getMobileNumber());
        $this->assertEquals(0.2, $this->obj->getFee());
        $this->assertEquals('test_extra_infos', $this->obj->getExtraInfos());
        $this->assertFalse($this->obj->getActive());

        $this->assertEquals(22, $new_obj->getId());
        $this->assertEquals('test_title', $new_obj->getTitle());
        $this->assertEquals('test_salutation', $new_obj->getSalutation());
        $this->assertEquals('test_firstname', $new_obj->getFirstname());
        $this->assertEquals('test_lastname', $new_obj->getLastname());
        $this->assertEquals(33, $new_obj->getProviderId());
        $this->assertEquals('test_email', $new_obj->getEmail());
        $this->assertEquals('test_phone', $new_obj->getPhone());
        $this->assertEquals('test_mobile_number', $new_obj->getMobileNumber());
        $this->assertEquals(0.2, $new_obj->getFee());
        $this->assertEquals('test_new_string', $new_obj->getExtraInfos());
        $this->assertFalse($new_obj->getActive());
    }

    public function testWithActive() : void
    {
        $new_obj = $this->obj->withActive(true);

        $this->assertEquals(22, $this->obj->getId());
        $this->assertEquals('test_title', $this->obj->getTitle());
        $this->assertEquals('test_salutation', $this->obj->getSalutation());
        $this->assertEquals('test_firstname', $this->obj->getFirstname());
        $this->assertEquals('test_lastname', $this->obj->getLastname());
        $this->assertEquals(33, $this->obj->getProviderId());
        $this->assertEquals('test_email', $this->obj->getEmail());
        $this->assertEquals('test_phone', $this->obj->getPhone());
        $this->assertEquals('test_mobile_number', $this->obj->getMobileNumber());
        $this->assertEquals(0.2, $this->obj->getFee());
        $this->assertEquals('test_extra_infos', $this->obj->getExtraInfos());
        $this->assertFalse($this->obj->getActive());

        $this->assertEquals(22, $new_obj->getId());
        $this->assertEquals('test_title', $new_obj->getTitle());
        $this->assertEquals('test_salutation', $new_obj->getSalutation());
        $this->assertEquals('test_firstname', $new_obj->getFirstname());
        $this->assertEquals('test_lastname', $new_obj->getLastname());
        $this->assertEquals(33, $new_obj->getProviderId());
        $this->assertEquals('test_email', $new_obj->getEmail());
        $this->assertEquals('test_phone', $new_obj->getPhone());
        $this->assertEquals('test_mobile_number', $new_obj->getMobileNumber());
        $this->assertEquals(0.2, $new_obj->getFee());
        $this->assertEquals('test_extra_infos', $new_obj->getExtraInfos());
        $this->assertTrue($new_obj->getActive());
    }
}

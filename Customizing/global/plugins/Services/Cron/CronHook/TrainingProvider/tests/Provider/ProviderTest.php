<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\TrainingProvider\Provider;

use PHPUnit\Framework\TestCase;
use CaT\Plugins\TrainingProvider\Trainer\Trainer;
use CaT\Plugins\TrainingProvider\Tags\Tag;
use Exception;
use ReflectionClass;
use ReflectionMethod;

class ProviderTest extends TestCase
{
    const ID = 22;
    const TEST_STRING_1 = 'test_string_1';
    const TEST_STRING_2 = 'test_string_2';
    const TEST_FLOAT_1 = 0.1;
    const TEST_FLOAT_2 = 0.2;
    const TEST_BOOL_TRUE = true;
    const TEST_BOOL_FALSE = false;

    const F_ID = 'getId';
    const F_NAME = 'getName';
    const F_RATING = 'getRating';
    const F_INFO = 'getInfo';
    const F_ADDRESS1 = 'getAddress1';
    const F_COUNTRY = 'getCountry';
    const F_ADDRESS2 = 'getAddress2';
    const F_POSTCODE = 'getPostcode';
    const F_CITY = 'getCity';
    const F_HOMEPAGE = 'getHomepage';
    const F_INTERNAL_CONTACT = 'getInternalContact';
    const F_CONTACT = 'getContact';
    const F_PHONE = 'getPhone';
    const F_FAX = 'getFax';
    const F_EMAIL = 'getEmail';
    const F_GENERAL_AGREEMENT = 'getGeneralAgreement';
    const F_TERMS = 'getTerms';
    const F_VALUTA = 'getValuta';
    const F_TRAINER = 'getTrainer';
    const F_TAGS = 'getTags';

    /**
     * @var array
     */
    protected $methods;

    /**
     * @var Provider
     */
    protected $obj;

    public function setUp() : void
    {
        $trainer = new Trainer(
            11,
            'title',
            'salutation',
            'firstname',
            'lastname'
        );

        $tag = new Tag(
            33,
            'name',
            'color'
        );

        $this->obj = new Provider(
            self::ID,
            self::TEST_STRING_1,
            self::TEST_FLOAT_1,
            self::TEST_STRING_1,
            self::TEST_STRING_1,
            self::TEST_STRING_1,
            self::TEST_STRING_1,
            self::TEST_STRING_1,
            self::TEST_STRING_1,
            self::TEST_STRING_1,
            self::TEST_STRING_1,
            self::TEST_STRING_1,
            self::TEST_STRING_1,
            self::TEST_STRING_1,
            self::TEST_STRING_1,
            self::TEST_BOOL_TRUE,
            self::TEST_STRING_1,
            self::TEST_STRING_1,
            [$trainer],
            [$tag]
        );
    }

    public function testObjCreation() : void
    {
        $this->assertInstanceOf(Provider::class, $this->obj);

        $this->originalOk();
    }

    public function testWithName() : void
    {
        $new_obj = $this->obj->withName(self::TEST_STRING_2);

        $this->originalOk();
        $this->newObjOk($new_obj, self::F_NAME);
    }

    public function testWithRating() : void
    {
        $new_obj = $this->obj->withRating(self::TEST_FLOAT_2);

        $this->originalOk();
        $this->newObjOk($new_obj, self::F_RATING);
    }

    public function testWithInfo() : void
    {
        $new_obj = $this->obj->withInfo(self::TEST_STRING_2);

        $this->originalOk();
        $this->newObjOk($new_obj, self::F_INFO);
    }

    public function testWithAddress1() : void
    {
        $new_obj = $this->obj->withAddress1(self::TEST_STRING_2);

        $this->originalOk();
        $this->newObjOk($new_obj, self::F_ADDRESS1);
    }

    public function testWithAddress2() : void
    {
        $new_obj = $this->obj->withAddress2(self::TEST_STRING_2);

        $this->originalOk();
        $this->newObjOk($new_obj, self::F_ADDRESS2);
    }

    public function testWithCountry() : void
    {
        $new_obj = $this->obj->withCountry(self::TEST_STRING_2);

        $this->originalOk();
        $this->newObjOk($new_obj, self::F_COUNTRY);
    }

    public function testWithPostcode() : void
    {
        $new_obj = $this->obj->withPostcode(self::TEST_STRING_2);

        $this->originalOk();
        $this->newObjOk($new_obj, self::F_POSTCODE);
    }

    public function testWithCity() : void
    {
        $new_obj = $this->obj->withCity(self::TEST_STRING_2);

        $this->originalOk();
        $this->newObjOk($new_obj, self::F_CITY);
    }

    public function testWithHomepage() : void
    {
        $new_obj = $this->obj->withHomepage(self::TEST_STRING_2);

        $this->originalOk();
        $this->newObjOk($new_obj, self::F_HOMEPAGE);
    }

    public function testWithInternalContact() : void
    {
        $new_obj = $this->obj->withInternalContact(self::TEST_STRING_2);

        $this->originalOk();
        $this->newObjOk($new_obj, self::F_INTERNAL_CONTACT);
    }

    public function testWithContact() : void
    {
        $new_obj = $this->obj->withContact(self::TEST_STRING_2);

        $this->originalOk();
        $this->newObjOk($new_obj, self::F_CONTACT);
    }

    public function testWithPhone() : void
    {
        $new_obj = $this->obj->withPhone(self::TEST_STRING_2);

        $this->originalOk();
        $this->newObjOk($new_obj, self::F_PHONE);
    }

    public function testWithFax() : void
    {
        $new_obj = $this->obj->withFax(self::TEST_STRING_2);

        $this->originalOk();
        $this->newObjOk($new_obj, self::F_FAX);
    }

    public function testWithEmail() : void
    {
        $new_obj = $this->obj->withEmail(self::TEST_STRING_2);

        $this->originalOk();
        $this->newObjOk($new_obj, self::F_EMAIL);
    }

    public function testWithGeneralAgreement() : void
    {
        $new_obj = $this->obj->withGeneralAgreement(self::TEST_BOOL_FALSE);

        $this->originalOk();
        $this->newObjOk($new_obj, self::F_GENERAL_AGREEMENT);
    }

    public function testWithTerms() : void
    {
        $new_obj = $this->obj->withTerms(self::TEST_STRING_2);

        $this->originalOk();
        $this->newObjOk($new_obj, self::F_TERMS);
    }

    public function testWithValuta() : void
    {
        $new_obj = $this->obj->withValuta(self::TEST_STRING_2);

        $this->originalOk();
        $this->newObjOk($new_obj, self::F_VALUTA);
    }

    public function testWithTrainer() : void
    {
        $new_obj = $this->obj->withTrainer(['test']);

        $this->originalOk();
        $this->newObjOk($new_obj, self::F_TRAINER);
    }

    public function testWithTags() : void
    {
        $new_obj = $this->obj->withTags(['test']);

        $this->originalOk();
        $this->newObjOk($new_obj, self::F_TAGS);
    }

    protected function originalOk() : void
    {
        $this->assertEquals(self::ID, $this->obj->getId());
        $this->assertEquals(self::TEST_STRING_1, $this->obj->getName());
        $this->assertEquals(self::TEST_FLOAT_1, $this->obj->getRating());
        $this->assertEquals(self::TEST_STRING_1, $this->obj->getInfo());
        $this->assertEquals(self::TEST_STRING_1, $this->obj->getAddress1());
        $this->assertEquals(self::TEST_STRING_1, $this->obj->getAddress2());
        $this->assertEquals(self::TEST_STRING_1, $this->obj->getCountry());
        $this->assertEquals(self::TEST_STRING_1, $this->obj->getPostcode());
        $this->assertEquals(self::TEST_STRING_1, $this->obj->getCity());
        $this->assertEquals(self::TEST_STRING_1, $this->obj->getHomepage());
        $this->assertEquals(self::TEST_STRING_1, $this->obj->getInternalContact());
        $this->assertEquals(self::TEST_STRING_1, $this->obj->getContact());
        $this->assertEquals(self::TEST_STRING_1, $this->obj->getPhone());
        $this->assertEquals(self::TEST_STRING_1, $this->obj->getFax());
        $this->assertEquals(self::TEST_STRING_1, $this->obj->getEmail());
        $this->assertTrue($this->obj->getGeneralAgreement());
        $this->assertEquals(self::TEST_STRING_1, $this->obj->getTerms());
        $this->assertEquals(self::TEST_STRING_1, $this->obj->getValuta());
        $this->assertIsArray($this->obj->getTrainer());
        $this->assertInstanceOf(Trainer::class, $this->obj->getTrainer()[0]);
        $this->assertIsArray($this->obj->getTags());
        $this->assertInstanceOf(Tag::class, $this->obj->getTags()[0]);
    }

    protected function newObjOk($new_obj, string $name) : void
    {
        $methods = $this->getFunctionNames();
        foreach ($methods as $method) {
            $f = $method->getName();
            switch ($f) {
                case self::F_ID:
                    $this->assertEquals(self::ID, $new_obj->$f());
                    break;
                case self::F_NAME:
                case self::F_INFO:
                case self::F_ADDRESS1:
                case self::F_COUNTRY:
                case self::F_ADDRESS2:
                case self::F_POSTCODE:
                case self::F_CITY:
                case self::F_HOMEPAGE:
                case self::F_INTERNAL_CONTACT:
                case self::F_CONTACT:
                case self::F_PHONE:
                case self::F_FAX:
                case self::F_EMAIL:
                case self::F_TERMS:
                case self::F_VALUTA:
                    if ($name === $f) {
                        $this->assertEquals(self::TEST_STRING_2, $new_obj->$f());
                        break;
                    }
                    $this->assertEquals(self::TEST_STRING_1, $new_obj->$f());
                    break;
                case self::F_RATING:
                    if ($name === $f) {
                        $this->assertEquals(self::TEST_FLOAT_2, $new_obj->$f());
                        break;
                    }
                    $this->assertEquals(self::TEST_FLOAT_1, $new_obj->$f());
                    break;
                case self::F_GENERAL_AGREEMENT:
                    if ($name === $f) {
                        $this->assertFalse($new_obj->$f());
                        break;
                    }
                    $this->assertTrue($new_obj->getGeneralAgreement());
                    break;
                case self::F_TRAINER:
                case self::F_TAGS:
                    if ($name === $f) {
                        $this->assertIsArray($new_obj->$f());
                        $this->assertEquals('test', $new_obj->$f()[0]);
                        break;
                    }
                    break;
                default:
                    throw new Exception("Unknown method for testing: " . $f);
                    break;
            }
        }
    }

    protected function getFunctionNames() : array
    {
        if (is_null($this->methods)) {
            $class = new ReflectionClass(Provider::class);
            $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
            $this->methods = array_filter($methods, function ($method) {
                return strpos($method->getName(), 'get') !== false;
            });
        }
        return $this->methods;
    }
}

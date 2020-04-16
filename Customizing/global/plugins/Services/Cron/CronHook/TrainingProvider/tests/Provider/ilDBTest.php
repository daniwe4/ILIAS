<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\TrainingProvider\Provider;

use PHPUnit\Framework\TestCase;
use CaT\Plugins\TrainingProvider\Mocks;

class ilDBTest extends TestCase
{
    const TABLE_NAME = "tp_provider";
    const TABLE_TAGS = "tp_tags";
    const TABLE_TAGS_ALLOCATION = "tp_tags_provider";
    const TABLE_TRAINER = "tp_trainer";

    const NEW_LINE_DELIMITER = "#nl#";
    const TAG_DELIMITER = "#:#";
    const TAGS_DELIMITER = "#|#";

    /**
     * @var Mocks
     */
    protected $mocks;

    public function setUp() : void
    {
        $this->mocks = new Mocks();
    }

    public function testCreateInstance() : void
    {
        $db = new ilDB($this->mocks->getIliasDBMock());
        $this->assertInstanceOf(ilDB::class, $db);
    }

    public function testCreateWithOnlyName() : void
    {
        $provider = new Provider(1, 'test_name');

        $values = [
            'id' => ['integer', $provider->getId()],
            'name' => ['text', $provider->getName()],
            'rating' => ['float', $provider->getRating()],
            'info' => ['text', $provider->getInfo()],
            'address1' => ['text', $provider->getAddress1()],
            'country' => ['text', $provider->getCountry()],
            'address2' => ['text', $provider->getAddress2()],
            'postcode' => ['text', $provider->getPostcode()],
            'city' => ['text', $provider->getCity()],
            'homepage' => ['text', $provider->getHomepage()],
            'internal_contact' => ['text', $provider->getInternalContact()],
            'contact' => ['text', $provider->getContact()],
            'phone' => ['text', $provider->getPhone()],
            'fax' => ['text', $provider->getFax()],
            'email' => ['text', $provider->getEmail()],
            'general_agreement' => ['integer', $provider->getGeneralAgreement()],
            'terms' => ['text', $provider->getTerms()],
            'valuta' => ['text', $provider->getValuta()]
        ];

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('nextId')
            ->willReturn(1)
        ;
        $db_mock
            ->expects($this->once())
            ->method('insert')
            ->with(self::TABLE_NAME, $values)
        ;

        $db = new ilDB($db_mock);

        $result = $db->create('test_name');

        $this->assertEquals(1, $result->getId());
        $this->assertEquals('test_name', $result->getName());
        $this->assertEquals(0.0, $result->getRating());
        $this->assertEquals('', $result->getInfo());
        $this->assertEquals('', $result->getAddress1());
        $this->assertEquals('', $result->getCountry());
        $this->assertEquals('', $result->getAddress2());
        $this->assertEquals('', $result->getPostcode());
        $this->assertEquals('', $result->getCity());
        $this->assertEquals('', $result->getHomepage());
        $this->assertEquals('', $result->getInternalContact());
        $this->assertEquals('', $result->getContact());
        $this->assertEquals('', $result->getPhone());
        $this->assertEquals('', $result->getFax());
        $this->assertEquals('', $result->getEmail());
        $this->assertFalse($result->getGeneralAgreement());
        $this->assertEquals('', $result->getTerms());
        $this->assertEquals('', $result->getValuta());
    }

    public function testCreateWithValues() : void
    {
        $provider = new Provider(
            1,
            'test_name',
            0.2,
            'test_info',
            'test_address1',
            'test_country',
            'test_address2',
            'test_postcode',
            'test_city',
            'test_homepage',
            'test_internal_contact',
            'test_contact',
            'test_phone',
            'test_fax',
            'test_email',
            true,
            'test_terms',
            'test_valuta'
        );

        $values = [
            'id' => ['integer', $provider->getId()],
            'name' => ['text', $provider->getName()],
            'rating' => ['float', $provider->getRating()],
            'info' => ['text', $provider->getInfo()],
            'address1' => ['text', $provider->getAddress1()],
            'country' => ['text', $provider->getCountry()],
            'address2' => ['text', $provider->getAddress2()],
            'postcode' => ['text', $provider->getPostcode()],
            'city' => ['text', $provider->getCity()],
            'homepage' => ['text', $provider->getHomepage()],
            'internal_contact' => ['text', $provider->getInternalContact()],
            'contact' => ['text', $provider->getContact()],
            'phone' => ['text', $provider->getPhone()],
            'fax' => ['text', $provider->getFax()],
            'email' => ['text', $provider->getEmail()],
            'general_agreement' => ['integer', $provider->getGeneralAgreement()],
            'terms' => ['text', $provider->getTerms()],
            'valuta' => ['text', $provider->getValuta()]
        ];

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('nextId')
            ->willReturn(1)
        ;
        $db_mock
            ->expects($this->once())
            ->method('insert')
            ->with(self::TABLE_NAME, $values)
        ;

        $db = new ilDB($db_mock);

        $result = $db->create(
            'test_name',
            0.2,
            'test_info',
            'test_address1',
            'test_country',
            'test_address2',
            'test_postcode',
            'test_city',
            'test_homepage',
            'test_internal_contact',
            'test_contact',
            'test_phone',
            'test_fax',
            'test_email',
            true,
            'test_terms',
            'test_valuta'
        );

        $this->assertEquals(1, $result->getId());
        $this->assertEquals('test_name', $result->getName());
        $this->assertEquals(0.2, $result->getRating());
        $this->assertEquals('test_info', $result->getInfo());
        $this->assertEquals('test_address1', $result->getAddress1());
        $this->assertEquals('test_country', $result->getCountry());
        $this->assertEquals('test_address2', $result->getAddress2());
        $this->assertEquals('test_postcode', $result->getPostcode());
        $this->assertEquals('test_city', $result->getCity());
        $this->assertEquals('test_homepage', $result->getHomepage());
        $this->assertEquals('test_internal_contact', $result->getInternalContact());
        $this->assertEquals('test_contact', $result->getContact());
        $this->assertEquals('test_phone', $result->getPhone());
        $this->assertEquals('test_fax', $result->getFax());
        $this->assertEquals('test_email', $result->getEmail());
        $this->assertTrue($result->getGeneralAgreement());
        $this->assertEquals('test_terms', $result->getTerms());
        $this->assertEquals('test_valuta', $result->getValuta());
    }

    public function testSelectWithEmptyResult() : void
    {
        $sql =
              "SELECT prov.name, prov.rating, prov.info, prov.address1, prov.country, prov.address2, prov.postcode, prov.city" . PHP_EOL
            . ", prov.homepage, prov.internal_contact, prov.contact, prov.phone, prov.fax, prov.email, prov.general_agreement, prov.terms, prov.valuta" . PHP_EOL
            . ", GROUP_CONCAT(alloc.id SEPARATOR '" . self::TAGS_DELIMITER . "') as tags" . PHP_EOL
            . "FROM " . self::TABLE_NAME . " prov" . PHP_EOL
            . "LEFT JOIN " . self::TABLE_TAGS_ALLOCATION . " alloc" . PHP_EOL
            . "    ON alloc.provider_id = prov.id" . PHP_EOL
            . "WHERE prov.id = 22" . PHP_EOL
            . "GROUP BY prov.id" . PHP_EOL
        ;

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('quote')
            ->with(22, 'integer')
            ->willReturn(22)
        ;
        $db_mock
            ->expects($this->once())
            ->method('query')
            ->with($sql)
            ->willReturn([])
        ;
        $db_mock
            ->expects($this->once())
            ->method('numRows')
            ->with([])
            ->willReturn(0)
        ;

        $db = new ilDB($db_mock);

        try {
            $db->select(22);
            $this->assertTrue(false);
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
    }

    public function testSelectWithResult() : void
    {
        $sql =
            "SELECT prov.name, prov.rating, prov.info, prov.address1, prov.country, prov.address2, prov.postcode, prov.city" . PHP_EOL
            . ", prov.homepage, prov.internal_contact, prov.contact, prov.phone, prov.fax, prov.email, prov.general_agreement, prov.terms, prov.valuta" . PHP_EOL
            . ", GROUP_CONCAT(alloc.id SEPARATOR '" . self::TAGS_DELIMITER . "') as tags" . PHP_EOL
            . "FROM " . self::TABLE_NAME . " prov" . PHP_EOL
            . "LEFT JOIN " . self::TABLE_TAGS_ALLOCATION . " alloc" . PHP_EOL
            . "    ON alloc.provider_id = prov.id" . PHP_EOL
            . "WHERE prov.id = 22" . PHP_EOL
            . "GROUP BY prov.id" . PHP_EOL
        ;

        $values = [
            'id' => 22,
            'name' => 'test_name',
            'rating' => 0.2,
            'info' => 'test_info',
            'address1' => 'test_address1',
            'country' => 'test_country',
            'address2' => 'test_address2',
            'postcode' => 'test_postcode',
            'city' => 'test_city',
            'homepage' => 'test_homepage',
            'internal_contact' => 'test_internal_contact',
            'contact' => 'test_contact',
            'phone' => 'test_phone',
            'fax' => 'test_fax',
            'email' => 'test_email',
            'general_agreement' => true,
            'terms' => 'test_terms',
            'valuta' => 'test_valuta'
        ];

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('quote')
            ->with(22, 'integer')
            ->willReturn(22)
        ;
        $db_mock
            ->expects($this->once())
            ->method('query')
            ->with($sql)
            ->willReturn([$values])
        ;
        $db_mock
            ->expects($this->once())
            ->method('numRows')
            ->with([$values])
            ->willReturn(1)
        ;
        $db_mock
            ->expects($this->atLeastOnce())
            ->method('fetchAssoc')
            ->with([$values])
            ->willReturn($values)
        ;

        $db = new ilDB($db_mock);

        $result = $db->select(22);

        $this->assertEquals(22, $result->getId());
        $this->assertEquals('test_name', $result->getName());
        $this->assertEquals(0.2, $result->getRating());
        $this->assertEquals('test_info', $result->getInfo());
        $this->assertEquals('test_address1', $result->getAddress1());
        $this->assertEquals('test_country', $result->getCountry());
        $this->assertEquals('test_address2', $result->getAddress2());
        $this->assertEquals('test_postcode', $result->getPostcode());
        $this->assertEquals('test_city', $result->getCity());
        $this->assertEquals('test_homepage', $result->getHomepage());
        $this->assertEquals('test_internal_contact', $result->getInternalContact());
        $this->assertEquals('test_contact', $result->getContact());
        $this->assertEquals('test_phone', $result->getPhone());
        $this->assertEquals('test_fax', $result->getFax());
        $this->assertEquals('test_email', $result->getEmail());
        $this->assertTrue($result->getGeneralAgreement());
        $this->assertEquals('test_terms', $result->getTerms());
        $this->assertEquals('test_valuta', $result->getValuta());
    }

    public function testUpdate() : void
    {
        $provider = new Provider(
            1,
            'test_name',
            0.2,
            'test_info',
            'test_address1',
            'test_country',
            'test_address2',
            'test_postcode',
            'test_city',
            'test_homepage',
            'test_internal_contact',
            'test_contact',
            'test_phone',
            'test_fax',
            'test_email',
            true,
            'test_terms',
            'test_valuta'
        );

        $where = ["id" => ["integer", $provider->getId()]];

        $values = [
            "name" => ["text", $provider->getName()],
            "rating" => ["float", $provider->getRating()],
            "info" => ["text", $provider->getInfo()],
            "address1" => ["text", $provider->getAddress1()],
            "country" => ["text", $provider->getCountry()],
            "address2" => ["text", $provider->getAddress2()],
            "postcode" => ["text", $provider->getPostcode()],
            "city" => ["text", $provider->getCity()],
            "homepage" => ["text", $provider->getHomepage()],
            "internal_contact" => ["text", $provider->getInternalContact()],
            "contact" => ["text", $provider->getContact()],
            "phone" => ["text", $provider->getPhone()],
            "fax" => ["text", $provider->getFax()],
            "email" => ["text", $provider->getEmail()],
            "general_agreement" => ["text", $provider->getGeneralAgreement()],
            "terms" => ["text", $provider->getTerms()],
            "valuta" => ["text", $provider->getValuta()]
        ];

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('update')
            ->with(self::TABLE_NAME, $values, $where)
        ;

        $db = new ilDB($db_mock);

        try {
            $db->update($provider);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }
    }

    public function testDelete()
    {
        $sql =
            'DELETE FROM ' . self::TABLE_NAME . PHP_EOL
            . ' WHERE id = 22'
        ;

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('quote')
            ->with(22, 'integer')
            ->willReturn(22)
        ;
        $db_mock
            ->expects($this->once())
            ->method('manipulate')
            ->with($sql)
        ;

        $db = new ilDB($db_mock);

        try {
            $db->delete(22);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }
    }

    public function testProviderNameExists() : void
    {
        $sql =
             'SELECT count(name) AS name' . PHP_EOL
            . 'FROM ' . self::TABLE_NAME . PHP_EOL
            . 'WHERE name = test_name' . PHP_EOL
        ;

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('quote')
            ->with('test_name', 'text')
            ->willReturn('test_name')
        ;
        $db_mock
            ->expects($this->once())
            ->method('query')
            ->with($sql)
            ->willReturn([['name' => 1]])
        ;
        $db_mock
            ->expects($this->once())
            ->method('fetchAssoc')
            ->with([['name' => 1]])
            ->willReturn(['name' => 1])
        ;

        $db = new ilDB($db_mock);

        $result = $db->providerNameExist('test_name');

        $this->assertTrue($result);
    }

    public function testGetProviderOverviewData() : void
    {
        $where = "";
        $sql =
             "SELECT prov.id, prov.name, prov.rating, prov.info" . PHP_EOL
            . "    , CONCAT_WS('" . self::NEW_LINE_DELIMITER . "', prov.address1, prov.address2, prov.country, prov.postcode, prov.city) AS address" . PHP_EOL
            . "    , prov.homepage, prov.internal_contact" . PHP_EOL
            . "    , CONCAT_WS('" . self::NEW_LINE_DELIMITER . "', prov.contact, prov.phone, prov.fax, prov.email) AS contact" . PHP_EOL
            . "    , 'tags' AS tags" . PHP_EOL
            . "    , prov.general_agreement, prov.terms, prov.valuta" . PHP_EOL
            . "    , GROUP_CONCAT(DISTINCT CONCAT_WS(' ', train.salutation, train.title, train.firstname, CONCAT_WS(', ', train.lastname, train.firstname)) SEPARATOR '" . self::NEW_LINE_DELIMITER . "') as trainer" . PHP_EOL
            . "    , MIN(train.fee) AS min_fee, MAX(train.fee) AS max_fee" . PHP_EOL
            . "    , GROUP_CONCAT(DISTINCT CONCAT_WS('" . self::TAG_DELIMITER . "', tags.name, tags.color) SEPARATOR '" . self::TAGS_DELIMITER . "') as tags" . PHP_EOL
            . "FROM " . self::TABLE_NAME . " prov" . PHP_EOL
            . "LEFT JOIN " . self::TABLE_TRAINER . " train" . PHP_EOL
            . "    ON prov.id = train.provider_id" . PHP_EOL
            . "LEFT JOIN " . self::TABLE_TAGS_ALLOCATION . " talloc" . PHP_EOL
            . "    ON prov.id = talloc.provider_id" . PHP_EOL
            . "LEFT JOIN " . self::TABLE_TAGS . " tags" . PHP_EOL
            . "    ON talloc.id = tags.id" . PHP_EOL
            . $where . PHP_EOL
            . "GROUP BY prov.id, train.provider_id" . PHP_EOL
        ;

        $row = [
            'id' => 22,
            'name' => 'test_name',
            'rating' => 0.2,
            'info' => 'test_info',
            'address' =>
                 'address1' . self::NEW_LINE_DELIMITER
                . 'address2' . self::NEW_LINE_DELIMITER
                . 'country' . self::NEW_LINE_DELIMITER
                . 'postcode' . self::NEW_LINE_DELIMITER
                . 'city'
            ,
            'homepage' => 'test_homepage',
            'internal_contact' => 'test_internal_contact',
            'contact' =>
                 'contact' . self::NEW_LINE_DELIMITER
                . 'phone' . self::NEW_LINE_DELIMITER
                . 'fax' . self::NEW_LINE_DELIMITER
                . 'email'
            ,
            'general_agreement' => true,
            'terms' => 'test_terms',
            'valuta' => 'test_valuta',
            'tags' => ''
        ];

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('query')
            ->with($sql)
            ->willReturn([$row])
        ;
        $db_mock
            ->expects($this->atLeastOnce())
            ->method('fetchAssoc')
            ->with([$row])
            ->willReturnOnConsecutiveCalls($row, null)
        ;

        $db = new ilDB($db_mock);

        $result = $db->getProviderOverviewData([]);

        $this->assertIsArray($result);

        $result = $result[0];

        $this->assertEquals(22, $result['id']);
        $this->assertEquals('test_name', $result['name']);
        $this->assertEquals(0.2, $result['rating']);
        $this->assertEquals('test_info', $result['info']);
        $this->assertEquals('address1<br />address2<br />country<br />postcode<br />city', $result['address']);
        $this->assertEquals('test_homepage', $result['homepage']);
        $this->assertEquals('test_internal_contact', $result['internal_contact']);
        $this->assertEquals('contact<br />phone<br />fax<br />email', $result['contact']);
        $this->assertTrue($result['general_agreement']);
        $this->assertEquals('test_terms', $result['terms']);
        $this->assertEquals('test_valuta', $result['valuta']);
        $this->assertIsArray($result['tags']);
    }

    public function testNameExistsPositive() : void
    {
        $sql =
            "SELECT COUNT(name) AS cnt" . PHP_EOL
            . "FROM " . self::TABLE_NAME . PHP_EOL
            . "WHERE name = test_name" . PHP_EOL
        ;

        $row1 = [
            'cnt' => 1
        ];

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('quote')
            ->with('test_name', 'text')
            ->willReturn('test_name')
        ;
        $db_mock
            ->expects($this->once())
            ->method('query')
            ->with($sql)
            ->willReturn([$row1])
        ;
        $db_mock
            ->expects($this->once())
            ->method('fetchAssoc')
            ->with([$row1])
            ->willReturn($row1)
        ;

        $db = new ilDB($db_mock);

        $result = $db->nameExists('test_name');
        $this->assertTrue($result);
    }

    public function testNameExistsNegative() : void
    {
        $sql =
            "SELECT COUNT(name) AS cnt" . PHP_EOL
            . "FROM " . self::TABLE_NAME . PHP_EOL
            . "WHERE name = test_name" . PHP_EOL
        ;

        $row1 = [
            'cnt' => 0
        ];

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('quote')
            ->with('test_name', 'text')
            ->willReturn('test_name')
        ;
        $db_mock
            ->expects($this->once())
            ->method('query')
            ->with($sql)
            ->willReturn([$row1])
        ;
        $db_mock
            ->expects($this->once())
            ->method('fetchAssoc')
            ->with([$row1])
            ->willReturn($row1)
        ;

        $db = new ilDB($db_mock);

        $result = $db->nameExists('test_name');
        $this->assertFalse($result);
    }

    public function testGetCurrentProviderName() : void
    {
        $sql =
            "SELECT name" . PHP_EOL
            . "FROM " . self::TABLE_NAME . "" . PHP_EOL
            . "WHERE id = 22" . PHP_EOL
        ;

        $row1 = [
            'name' => 'test_name'
        ];

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('quote')
            ->with(22, 'integer')
            ->willReturn(22)
        ;
        $db_mock
            ->expects($this->once())
            ->method('query')
            ->with($sql)
            ->willReturn([$row1])
        ;
        $db_mock
            ->expects($this->once())
            ->method('fetchAssoc')
            ->with([$row1])
            ->willReturn($row1)
        ;

        $db = new ilDB($db_mock);

        $result = $db->getCurrentProviderName(22);
        $this->assertEquals('test_name', $result);
    }

    public function testGetProviderOptions() : void
    {
        $sql =
            "SELECT id, name" . PHP_EOL
            . "FROM " . self::TABLE_NAME . PHP_EOL
            . "ORDER BY name ASC" . PHP_EOL
        ;

        $row1 = [
            'id' => 22,
            'name' => 'test_name'
        ];

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('query')
            ->with($sql)
            ->willReturnOnConsecutiveCalls([$row1])
        ;
        $db_mock
            ->expects($this->atLeastOnce())
            ->method('fetchAssoc')
            ->with([$row1])
            ->willReturnOnConsecutiveCalls($row1, null)
        ;

        $db = new ilDB($db_mock);

        $result = $db->getProviderOptions();
        $this->assertEquals('test_name', $result[22]);
    }

    public function testGetAllProvidersOrdered() : void
    {
        $sql =
            "SELECT id,name,rating,info,address1,country,address2," . PHP_EOL
            . "postcode,city,homepage,internal_contact,contact," . PHP_EOL
            . "phone,fax,email,general_agreement,terms,valuta" . PHP_EOL
            . "FROM " . self::TABLE_NAME . PHP_EOL
            . "ORDER BY info ASC" . PHP_EOL
        ;

        $values = [
            'id' => 22,
            'name' => 'test_name',
            'rating' => 0.2,
            'info' => 'test_info',
            'address1' => 'test_address1',
            'country' => 'test_country',
            'address2' => 'test_address2',
            'postcode' => 'test_postcode',
            'city' => 'test_city',
            'homepage' => 'test_homepage',
            'internal_contact' => 'test_internal_contact',
            'contact' => 'test_contact',
            'phone' => 'test_phone',
            'fax' => 'test_fax',
            'email' => 'test_email',
            'general_agreement' => true,
            'terms' => 'test_terms',
            'valuta' => 'test_valuta',
            'tags' => ''
        ];

        $db_mock = $this->mocks->getIliasDBMock();
        $db_mock
            ->expects($this->once())
            ->method('query')
            ->with($sql)
            ->willReturn([$values])
        ;
        $db_mock
            ->expects($this->atLeastOnce())
            ->method('fetchAssoc')
            ->with([$values])
            ->willReturnOnConsecutiveCalls($values, null)
        ;

        $db = new ilDB($db_mock);

        $result = $db->getAllProviders('info', 'ASC');

        $result = $result[0];

        $this->assertEquals(22, $result->getId());
        $this->assertEquals('test_name', $result->getName());
        $this->assertEquals(0.2, $result->getRating());
        $this->assertEquals('test_info', $result->getInfo());
        $this->assertEquals('test_address1', $result->getAddress1());
        $this->assertEquals('test_country', $result->getCountry());
        $this->assertEquals('test_address2', $result->getAddress2());
        $this->assertEquals('test_postcode', $result->getPostcode());
        $this->assertEquals('test_city', $result->getCity());
        $this->assertEquals('test_homepage', $result->getHomepage());
        $this->assertEquals('test_internal_contact', $result->getInternalContact());
        $this->assertEquals('test_contact', $result->getContact());
        $this->assertEquals('test_phone', $result->getPhone());
        $this->assertEquals('test_fax', $result->getFax());
        $this->assertEquals('test_email', $result->getEmail());
        $this->assertTrue($result->getGeneralAgreement());
        $this->assertEquals('test_terms', $result->getTerms());
        $this->assertEquals('test_valuta', $result->getValuta());
    }
}

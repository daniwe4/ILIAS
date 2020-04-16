<?php

use \CaT\Plugins\Venues\ObjectFactory;
use \CaT\Plugins\Venues\Venues\Contact\Contact;
use PHPUnit\Framework\TestCase;

/**
 * Test the settings of Venue
 *
  * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ContactTest extends TestCase
{
    use ObjectFactory;

    public function testConstruction()
    {
        $id = 1;
        $internal_contact = 'internal contact';
        $contact = 'contact';
        $phone = '(01234) 11223344 - 55';
        $fax = '(01234) 11223344 - 56';
        $email = 'mail@domain.com';


        $add = $this->getContactObject(
            $id,
            $internal_contact,
            $contact,
            $phone,
            $fax,
            $email
        );

        $this->assertInstanceOf(Contact::class, $add);

        $this->assertEquals($id, $add->getId());
        $this->assertEquals($internal_contact, $add->getInternalContact());
        $this->assertEquals($contact, $add->getContact());
        $this->assertEquals($phone, $add->getPhone());
        $this->assertEquals($fax, $add->getFax());
        $this->assertEquals($email, $add->getEmail());
    }
}

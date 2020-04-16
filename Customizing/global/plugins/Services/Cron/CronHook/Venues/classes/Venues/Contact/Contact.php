<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues\Contact;

/**
 * Venue configuration entries for contact settings
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class Contact
{
    /**
     * @var int
     */
    protected $id;

    /**
     * Name of internal contact person for venue
     *
     * @var string
     */
    protected $internal_contact = "";

    /**
     * Name of contact person at venue
     *
     * @var string
     */
    protected $contact = "";

    /**
     * Phone number of venue
     *
     * @var string
     */
    protected $phone = "";

    /**
     * Fax number of venue
     *
     * @var string
     */
    protected $fax = "";

    /**
     * Email address of venue
     *
     * @var string
     */
    protected $email = "";

    public function __construct(
        int $id,
        string $internal_contact = "",
        string $contact = "",
        string $phone = "",
        string $fax = "",
        string $email = ""
    ) {
        assert('is_int($id)');
        assert('is_string($internal_contact)');
        assert('is_string($contact)');
        assert('is_string($phone)');
        assert('is_string($fax)');
        assert('is_string($email)');

        $this->id = $id;
        $this->internal_contact = $internal_contact;
        $this->contact = $contact;
        $this->phone = $phone;
        $this->fax = $fax;
        $this->email = $email;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getInternalContact() : string
    {
        return $this->internal_contact;
    }

    public function getContact() : string
    {
        return $this->contact;
    }

    public function getPhone() : string
    {
        return $this->phone;
    }

    public function getFax() : string
    {
        return $this->fax;
    }

    public function getEmail() : string
    {
        return $this->email;
    }
}

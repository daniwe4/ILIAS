<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace CaT\Plugins\TrainingProvider\Provider;

/**
 * Object class for a single provider
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class Provider
{

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name = "";

    /**
     * @var float
     */
    protected $rating = 0;

    /**
     * @var string
     */
    protected $info = "";

    /**
     * @var string
     */
    protected $address1 = "";

    /**
     * @var string
     */
    protected $country = "";

    /**
     * @var string
     */
    protected $address2 = "";

    /**
     * @var string
     */
    protected $postcode = "";

    /**
     * @var string
     */
    protected $city = "";

    /**
     * @var string
     */
    protected $homepage = "";

    /**
     * @var string
     */
    protected $internal_contact = "";

    /**
     * @var string
     */
    protected $contact = "";

    /**
     * @var string
     */
    protected $phone = "";

    /**
     * @var string
     */
    protected $fax = "";

    /**
     * @var string
     */
    protected $email = "";

    /**
     * @var string
     */
    protected $general_agreement = "";

    /**
     * @var string
     */
    protected $terms = "";

    /**
     * @var string
     */
    protected $valuta = "";

    /**
     * @var \CaT\Plugins\TrainingProvider\Trainer\Trainer[] | []
     */
    protected $trainer = array();

    /**
     * @var \CaT\Plugins\TrainingProvider\Tags\Tag[] | []
     */
    protected $tags = array();

    public function __construct($id, $name, $rating = 0.0, $info = "", $address1 = "", $country = "", $address2 = "", $postcode = "", $city = "", $homepage = "", $internal_contact = "", $contact = "", $phone = "", $fax = "", $email = "", $general_agreement = false, $terms = "", $valuta = "", array $trainer = array(), array $tags = array())
    {
        assert('is_int($id)');
        assert('is_string($name)');
        assert('is_float($rating)');
        assert('is_string($info)');
        assert('is_string($address1)');
        assert('is_string($country)');
        assert('is_string($address2)');
        assert('is_string($postcode)');
        assert('is_string($city)');
        assert('is_string($homepage)');
        assert('is_string($internal_contact)');
        assert('is_string($contact)');
        assert('is_string($phone)');
        assert('is_string($fax)');
        assert('is_string($email)');
        assert('is_bool($general_agreement)');
        assert('is_string($terms)');
        assert('is_string($valuta)');

        $this->id = $id;
        $this->name = $name;
        $this->rating = $rating;
        $this->info = $info;
        $this->address1 = $address1;
        $this->country = $country;
        $this->address2 = $address2;
        $this->postcode = $postcode;
        $this->city = $city;
        $this->homepage = $homepage;
        $this->internal_contact = $internal_contact;
        $this->contact = $contact;
        $this->phone = $phone;
        $this->fax = $fax;
        $this->email = $email;
        $this->general_agreement = $general_agreement;
        $this->terms = $terms;
        $this->valuta = $valuta;
        $this->trainer = $trainer;
        $this->tags = $tags;
    }

    public function withName($name)
    {
        assert('is_string($name)');
        $clone = clone $this;
        $clone->name = $name;
        return $clone;
    }

    public function withRating($rating)
    {
        assert('is_float($rating');
        $clone = clone $this;
        $clone->rating = $rating;
        return $clone;
    }

    public function withInfo($info)
    {
        assert('is_string($info)');
        $clone = clone $this;
        $clone->info = $info;
        return $clone;
    }

    public function withAddress1($address1)
    {
        assert('is_string($address1)');
        $clone = clone $this;
        $clone->address1 = $address1;
        return $clone;
    }

    public function withCountry($country)
    {
        assert('is_string($country)');
        $clone = clone $this;
        $clone->country = $country;
        return $clone;
    }

    public function withAddress2($address2)
    {
        assert('is_string($address2)');
        $clone = clone $this;
        $clone->address2 = $address2;
        return $clone;
    }

    public function withPostcode($postcode)
    {
        assert('is_string($postcode)');
        $clone = clone $this;
        $clone->postcode = $postcode;
        return $clone;
    }

    public function withCity($city)
    {
        assert('is_string($city)');
        $clone = clone $this;
        $clone->city = $city;
        return $clone;
    }

    public function withHomepage($homepage)
    {
        assert('is_string($homepage)');
        $clone = clone $this;
        $clone->homepage = $homepage;
        return $clone;
    }

    public function withInternalContact($internal_contact)
    {
        assert('is_string($internal_contact)');
        $clone = clone $this;
        $clone->internal_contact = $internal_contact;
        return $clone;
    }

    public function withContact($contact)
    {
        assert('is_string($contact)');
        $clone = clone $this;
        $clone->contact = $contact;
        return $clone;
    }

    public function withPhone($phone)
    {
        assert('is_string($phone)');
        $clone = clone $this;
        $clone->phone = $phone;
        return $clone;
    }

    public function withFax($fax)
    {
        assert('is_string($fax)');
        $clone = clone $this;
        $clone->fax = $fax;
        return $clone;
    }

    public function withEmail($email)
    {
        assert('is_string($email)');
        $clone = clone $this;
        $clone->email = $email;
        return $clone;
    }

    public function withGeneralAgreement($general_agreement)
    {
        assert('is_bool($general_agreement)');
        $clone = clone $this;
        $clone->general_agreement = $general_agreement;
        return $clone;
    }

    public function withTerms($terms)
    {
        assert('is_string($terms)');
        $clone = clone $this;
        $clone->terms = $terms;
        return $clone;
    }

    public function withValuta($valuta)
    {
        assert('is_string($valuta)');
        $clone = clone $this;
        $clone->valuta = $valuta;
        return $clone;
    }

    public function withTrainer(array $tainer)
    {
        $clone = clone $this;
        $clone->trainer = $trainer;
        return $clone;
    }

    public function withTags(array $tags)
    {
        $clone = clone $this;
        $clone->tags = $tags;
        return $clone;
    }

    /**
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * @return float
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     *
     * @return string
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     *
     * @return string
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     *
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     *
     * @return string
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     *
     * @return string
     */
    public function getHomepage()
    {
        return $this->homepage;
    }

    /**
     *
     * @return string
     */
    public function getInternalContact()
    {
        return $this->internal_contact;
    }

    /**
     *
     * @return string
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     *
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     *
     * @return string
     */
    public function getGeneralAgreement()
    {
        return $this->general_agreement;
    }

    /**
     *
     * @return string
     */
    public function getTerms()
    {
        return $this->terms;
    }

    /**
     *
     * @return string
     */
    public function getValuta()
    {
        return $this->valuta;
    }

    /**
     * Get all possible trainer of training provider
     *
     * @return \CaT\Plugins\TrainingProvider\Trainer\Trainer[] | []
     */
    public function getTrainer()
    {
        return $this->trainer;
    }

    /**
     * Get all tags of training provider
     *
     * @return \CaT\Plugins\TrainingProvider\Tags\Tag[] | []
     */
    public function getTags()
    {
        return $this->tags;
    }
}

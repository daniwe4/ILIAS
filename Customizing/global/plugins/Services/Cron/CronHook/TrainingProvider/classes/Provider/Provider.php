<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

declare(strict_types=1);

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

    public function __construct(
        int $id,
        string $name,
        float $rating = 0.0,
        string $info = "",
        string $address1 = "",
        string $country = "",
        string $address2 = "",
        string $postcode = "",
        string $city = "",
        string $homepage = "",
        string $internal_contact = "",
        string $contact = "",
        string $phone = "",
        string $fax = "",
        string $email = "",
        bool $general_agreement = false,
        string $terms = "",
        string $valuta = "",
        array $trainer = array(),
        array $tags = array()
    ) {
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

    public function withName(string $name) : Provider
    {
        $clone = clone $this;
        $clone->name = $name;
        return $clone;
    }

    public function withRating(float $rating) : Provider
    {
        $clone = clone $this;
        $clone->rating = $rating;
        return $clone;
    }

    public function withInfo(string $info) : Provider
    {
        $clone = clone $this;
        $clone->info = $info;
        return $clone;
    }

    public function withAddress1(string $address1) : Provider
    {
        $clone = clone $this;
        $clone->address1 = $address1;
        return $clone;
    }

    public function withCountry(string $country) : Provider
    {
        $clone = clone $this;
        $clone->country = $country;
        return $clone;
    }

    public function withAddress2(string $address2) : Provider
    {
        $clone = clone $this;
        $clone->address2 = $address2;
        return $clone;
    }

    public function withPostcode(string $postcode) : Provider
    {
        $clone = clone $this;
        $clone->postcode = $postcode;
        return $clone;
    }

    public function withCity(string $city) : Provider
    {
        $clone = clone $this;
        $clone->city = $city;
        return $clone;
    }

    public function withHomepage(string $homepage) : Provider
    {
        $clone = clone $this;
        $clone->homepage = $homepage;
        return $clone;
    }

    public function withInternalContact(string $internal_contact) : Provider
    {
        $clone = clone $this;
        $clone->internal_contact = $internal_contact;
        return $clone;
    }

    public function withContact(string $contact) : Provider
    {
        $clone = clone $this;
        $clone->contact = $contact;
        return $clone;
    }

    public function withPhone(string $phone) : Provider
    {
        $clone = clone $this;
        $clone->phone = $phone;
        return $clone;
    }

    public function withFax(string $fax) : Provider
    {
        $clone = clone $this;
        $clone->fax = $fax;
        return $clone;
    }

    public function withEmail(string $email) : Provider
    {
        $clone = clone $this;
        $clone->email = $email;
        return $clone;
    }

    public function withGeneralAgreement(bool $general_agreement) : Provider
    {
        $clone = clone $this;
        $clone->general_agreement = $general_agreement;
        return $clone;
    }

    public function withTerms(string $terms) : Provider
    {
        $clone = clone $this;
        $clone->terms = $terms;
        return $clone;
    }

    public function withValuta(string $valuta) : Provider
    {
        $clone = clone $this;
        $clone->valuta = $valuta;
        return $clone;
    }

    public function withTrainer(array $trainer) : Provider
    {
        $clone = clone $this;
        $clone->trainer = $trainer;
        return $clone;
    }

    public function withTags(array $tags) : Provider
    {
        $clone = clone $this;
        $clone->tags = $tags;
        return $clone;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getRating() : float
    {
        return $this->rating;
    }

    public function getInfo() : string
    {
        return $this->info;
    }

    public function getAddress1() : string
    {
        return $this->address1;
    }

    public function getCountry() : string
    {
        return $this->country;
    }

    public function getAddress2() : string
    {
        return $this->address2;
    }

    public function getPostcode() : string
    {
        return $this->postcode;
    }

    public function getCity() : string
    {
        return $this->city;
    }

    public function getHomepage() : string
    {
        return $this->homepage;
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

    public function getGeneralAgreement() : bool
    {
        return $this->general_agreement;
    }

    public function getTerms() : string
    {
        return $this->terms;
    }

    public function getValuta() : string
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

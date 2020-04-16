<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace CaT\Plugins\TrainingProvider\Trainer;

/**
 * Object class for a single trainer
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class Trainer
{

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $salutation;

    /**
     * @var string
     */
    protected $firstname;

    /**
     * @var string
     */
    protected $lastname;

    /**
     * @var int | null
     */
    protected $provider_id = null;

    /**
     * @var string
     */
    protected $email = "";

    /**
     * @var string
     */
    protected $phone = "";

    /**
     * @var string
     */
    protected $mobile_number;

    /**
     * @var float | null
     */
    protected $fee = null;

    /**
     * @var string | null
     */
    protected $extra_infos = null;

    /**
     * @var boolean
     */
    protected $active = true;

    public function __construct($id, $title, $salutation, $firstname, $lastname, $provider_id = null, $email = "", $phone = "", $mobile_number = "", $fee = null, $extra_infos = null, $active = true)
    {
        assert('is_int($id)');
        assert('is_string($title) || is_null($title)');
        assert('is_string($salutation) || is_null($title)');
        assert('is_string($firstname)');
        assert('is_string($lastname)');
        assert('is_null($provider_id) || is_int($provider_id)');
        assert('is_string($email)');
        assert('is_string($phone)');
        assert('is_string($mobile_number)');
        assert('is_null($fee) || is_float($fee)');
        assert('is_null($extra_infos) || is_string($extra_infos)');
        assert('is_bool($active)');

        $this->id = $id;
        $this->title = $title;
        $this->salutation = $salutation;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->provider_id = $provider_id;
        $this->email = $email;
        $this->phone = $phone;
        $this->mobile_number = $mobile_number;
        $this->fee = $fee;
        $this->extra_infos = $extra_infos;
        $this->active = $active;
    }

    public function withTitle($title)
    {
        assert('is_string($title)');
        $clone = clone $this;
        $clone->title = $title;
        return $clone;
    }

    public function withSalutation($salutation)
    {
        assert('is_string($salutation)');
        $clone = clone $this;
        $clone->salutation = $salutation;
        return $clone;
    }

    public function withFirstname($firstname)
    {
        assert('is_string($firstname)');
        $clone = clone $this;
        $clone->firstname = $firstname;
        return $clone;
    }

    public function withLastname($lastname)
    {
        assert('is_string($lastname');
        $clone = clone $this;
        $clone->lastname = $lastname;
        return $clone;
    }

    public function withProviderId($provider_id)
    {
        assert('is_null($provider_id) || is_int($provider_id)');
        $clone = clone $this;
        $clone->provider_id = $provider_id;
        return $clone;
    }

    public function withEmail($email)
    {
        assert('is_string($email)');
        $clone = clone $this;
        $clone->email = $email;
        return $clone;
    }

    public function withPhone($phone)
    {
        assert('is_string($phone)');
        $clone = clone $this;
        $clone->phone = $phone;
        return $clone;
    }

    public function withMobileNumber($mobile_number)
    {
        assert('is_string($mobile_number)');
        $clone = clone $this;
        $clone->mobile_number = $mobile_number;
        return $clone;
    }

    public function withFee($fee)
    {
        assert('is_null($fee) || is_float($fee)');
        $clone = clone $this;
        $clone->fee = $fee;
        return $clone;
    }

    public function withExtraInfos($extra_infos)
    {
        assert('is_null($extra_infos) || is_float($extra_infos)');
        $clone = clone $this;
        $clone->extra_infos = $extra_infos;
        return $clone;
    }

    public function withActive($active)
    {
        assert('is_bool($active)');
        $clone = clone $this;
        $clone->active = $active;
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
    public function getTitle()
    {
        return $this->title;
    }

    /**
     *
     * @return string
     */
    public function getSalutation()
    {
        return $this->salutation;
    }
    /**
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     *
     * @return null | int
     */
    public function getProviderId()
    {
        return $this->provider_id;
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
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     *
     * @return string
     */
    public function getMobileNumber()
    {
        return $this->mobile_number;
    }

    /**
     *
     * @return float | null
     */
    public function getFee()
    {
        return $this->fee;
    }

    /**
     *
     * @return float | null
     */
    public function getExtraInfos()
    {
        return $this->extra_infos;
    }

    /**
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }
}

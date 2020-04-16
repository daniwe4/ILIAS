<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

declare(strict_types=1);

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

    public function __construct(
        int $id,
        ?string $title,
        ?string $salutation,
        string $firstname,
        string $lastname,
        ?int $provider_id = null,
        string $email = "",
        string $phone = "",
        string $mobile_number = "",
        ?float $fee = null,
        ?string $extra_infos = null,
        bool $active = true
    ) {
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

    public function withTitle(string $title) : Trainer
    {
        $clone = clone $this;
        $clone->title = $title;
        return $clone;
    }

    public function withSalutation(string $salutation) : Trainer
    {
        $clone = clone $this;
        $clone->salutation = $salutation;
        return $clone;
    }

    public function withFirstname(string $firstname) : Trainer
    {
        $clone = clone $this;
        $clone->firstname = $firstname;
        return $clone;
    }

    public function withLastname(string $lastname) : Trainer
    {
        $clone = clone $this;
        $clone->lastname = $lastname;
        return $clone;
    }

    public function withProviderId(?int $provider_id) : Trainer
    {
        $clone = clone $this;
        $clone->provider_id = $provider_id;
        return $clone;
    }

    public function withEmail(string $email) : Trainer
    {
        $clone = clone $this;
        $clone->email = $email;
        return $clone;
    }

    public function withPhone(string $phone) : Trainer
    {
        $clone = clone $this;
        $clone->phone = $phone;
        return $clone;
    }

    public function withMobileNumber(string $mobile_number) : Trainer
    {
        $clone = clone $this;
        $clone->mobile_number = $mobile_number;
        return $clone;
    }

    public function withFee(?float $fee) : Trainer
    {
        $clone = clone $this;
        $clone->fee = $fee;
        return $clone;
    }

    public function withExtraInfos(?string $extra_infos) : Trainer
    {
        $clone = clone $this;
        $clone->extra_infos = $extra_infos;
        return $clone;
    }

    public function withActive(bool $active) : Trainer
    {
        $clone = clone $this;
        $clone->active = $active;
        return $clone;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getSalutation() : string
    {
        return $this->salutation;
    }

    public function getFirstname() : string
    {
        return $this->firstname;
    }

    public function getLastname() : string
    {
        return $this->lastname;
    }

    public function getProviderId() : ?int
    {
        return $this->provider_id;
    }

    public function getEmail() : string
    {
        return $this->email;
    }

    public function getPhone() : string
    {
        return $this->phone;
    }

    public function getMobileNumber() : string
    {
        return $this->mobile_number;
    }

    public function getFee() : ?float
    {
        return $this->fee;
    }

    public function getExtraInfos() : ?string
    {
        return $this->extra_infos;
    }

    public function getActive() : bool
    {
        return $this->active;
    }
}

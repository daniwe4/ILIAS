<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues\Address;

/**
 * Venue konfiguration entries for address settings
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class Address
{
    /**
     * @var int
     */
    protected $id;

    /**
     * First address line for e.g. street
     *
     * @var string
     */
    protected $address1 = "";

    /**
     * Country where venue is addressed
     *
     * @var string
     */
    protected $country = "";

    /**
     * Second line for some further address informations
     *
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
     * @var float
     */
    protected $latitude;

    /**
     * @var float
     */
    protected $longitude;

    /**
     * @var int
     */
    protected $zoom;

    public function __construct(
        int $id,
        string $address1 = "",
        string $country = "",
        string $address2 = "",
        string $postcode = "",
        string $city = "",
        float $latitude = 0.0,
        float $longitude = 0.0,
        int $zoom = 10
    ) {
        $this->id = $id;
        $this->address1 = $address1;
        $this->country = $country;
        $this->address2 = $address2;
        $this->postcode = $postcode;
        $this->city = $city;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->zoom = $zoom;
    }

    public function getId() : int
    {
        return $this->id;
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

    public function getLatitude() : float
    {
        return $this->latitude;
    }

    public function getLongitude() : float
    {
        return $this->longitude;
    }

    public function getZoom() : int
    {
        return $this->zoom;
    }
}

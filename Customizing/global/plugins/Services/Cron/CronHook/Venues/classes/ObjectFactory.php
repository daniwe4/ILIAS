<?php

namespace CaT\Plugins\Venues;

/**
 * Trait for functions needed to create several venue objects
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
trait ObjectFactory
{
    /**
     * Get a general config object
     *
     * @return Venues\General\General
     */
    public function getGeneralObject(
        int $id,
        string $name,
        string $homepage = "",
        array $tags = array(),
        array $search_tags = array()
    ) {
        return new Venues\General\General(
            $id,
            $name,
            $homepage,
            $tags,
            $search_tags
        );
    }

    /**
     * Get a rating config object
     *
     * @return Venues\Rating\Rating
     */
    public function getRatingObject(int $id, float $rating = 0.0, string $info = "")
    {
        return new Venues\Rating\Rating(
            $id,
            $rating,
            $info
        );
    }

    /**
     * Get an address config object
     *
     * @return Venues\Address\Address
     */
    public function getAddressObject(
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
        return new Venues\Address\Address(
            $id,
            $address1,
            $country,
            $address2,
            $postcode,
            $city,
            $latitude,
            $longitude,
            $zoom
        );
    }

    /**
     * Get a contact config object
     *
     * @return Venues\Contact\Contact
     */
    public function getContactObject(
        int $id,
        string $internal_contact = "",
        string $contact = "",
        string $phone = "",
        string $fax = "",
        string $email = ""
    ) {
        return new Venues\Contact\Contact(
            $id,
            $internal_contact,
            $contact,
            $phone,
            $fax,
            $email
        );
    }

    /**
     * Get a conditions config object
     *
     * @return Venues\Conditions\Conditions
     */
    public function getConditionsObject(
        int $id,
        bool $general_agreement = false,
        string $terms = "",
        string $valuta = ""
    ) {
        return new Venues\Conditions\Conditions(
            $id,
            $general_agreement,
            $terms,
            $valuta
        );
    }

    /**
     * Get a capacity config object
     *
     * @return Venues\Capacity\Capacity
     */
    public function getCapacityObject(
        int $id,
        ?int $number_rooms = null,
        ?int $min_person_any_room = null,
        ?int $max_person_any_room = null,
        ?int $min_room_size = null,
        ?int $max_room_size = null,
        ?int $room_count = null
    ) {
        return new Venues\Capacity\Capacity(
            $id,
            $number_rooms,
            $min_person_any_room,
            $max_person_any_room,
            $min_room_size,
            $max_room_size,
            $room_count
        );
    }

    /**
     * Get a service config object
     *
     * @param int 	$id
     * @param string 	$mail_service_list
     * @param string 	$mail_room_setup
     * @param int | null	$days_send_service
     * @param int | null	$days_send_room_setup
     * @param string 	$mail_material_list
     * @param int | null	$days_send_material_list
     * @param string 	$mail_accomodation_list
     * @param int | null 	$days_send_accomodation_list
     * @param int | null 	$days_remind_accomodation_list
     *
     * @return Venues\Service\Service
     */
    public function getServiceObject(
        int $id,
        string $mail_service_list = "",
        string $mail_room_setup = "",
        ?int $days_send_service = null,
        ?int $days_send_room_setup = null,
        string $mail_material_list = "",
        ?int $days_send_material_list = null,
        string $mail_accomodation_list = "",
        ?int $days_send_accomodation_list = null,
        ?int $days_remind_accomodation_list = null
    ) {
        return new Venues\Service\Service(
            $id,
            $mail_service_list,
            $mail_room_setup,
            $days_send_service,
            $days_send_room_setup,
            $mail_material_list,
            $days_send_material_list,
            $mail_accomodation_list,
            $days_send_accomodation_list,
            $days_remind_accomodation_list
        );
    }

    /**
     * Get a costs config object
     *
     * @return Venues\Costs\Costs
     */
    public function getCostsObject(
        int $id,
        ?float $fixed_rate_day = null,
        ?float $fixed_rate_all_inclusive = null,
        ?float $bed_and_breakfast = null,
        ?float $bed = null,
        ?float $fixed_rate_conference = null,
        ?float $room_usage = null,
        ?float $other = null,
        string $terms = ""
    ) {
        return new Venues\Costs\Costs(
            $id,
            $fixed_rate_day,
            $fixed_rate_all_inclusive,
            $bed_and_breakfast,
            $bed,
            $fixed_rate_conference,
            $room_usage,
            $other,
            $terms
            );
    }
}

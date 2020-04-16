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
    public function getGeneralObject($id, $name, $homepage = "", array $tags = array(), array $search_tags = array())
    {
        assert('is_int($id)');
        assert('is_string($name)');
        assert('is_string($homepage)');

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
    public function getRatingObject($id, $rating = 0.0, $info = "")
    {
        assert('is_int($id)');
        assert('is_float($rating)');
        assert('is_string($info)');

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
    public function getAddressObject($id, $address1 = "", $country = "", $address2 = "", $postcode = "", $city = "", $latitude = 0.0, $longitude = 0.0, $zoom = 10)
    {
        assert('is_int($id)');
        assert('is_string($address1)');
        assert('is_string($country)');
        assert('is_string($address2)');
        assert('is_string($postcode)');
        assert('is_string($city)');
        assert('is_float($latitude)');
        assert('is_float($longitude)');
        assert('is_int($zoom)');

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
    public function getContactObject($id, $internal_contact = "", $contact = "", $phone = "", $fax = "", $email = "")
    {
        assert('is_int($id)');
        assert('is_string($internal_contact)');
        assert('is_string($contact)');
        assert('is_string($phone)');
        assert('is_string($fax)');
        assert('is_string($email)');

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
    public function getConditionsObject($id, $general_agreement = false, $terms = "", $valuta = "")
    {
        assert('is_int($id)');
        assert('is_bool($general_agreement)');
        assert('is_string($terms)');
        assert('is_string($valuta)');

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
        $id,
        $number_rooms = null,
        $min_person_any_room = null,
        $max_person_any_room = null,
        $min_room_size = null,
        $max_room_size = null,
        $room_count = null
    ) {
        assert('is_int($id)');
        assert('is_null($number_rooms) | is_int($number_rooms)');
        assert('is_null($min_person_any_room) | is_int($min_person_any_room)');
        assert('is_null($max_person_any_room) | is_int($max_person_any_room)');
        assert('is_null($min_room_size) | is_int($min_room_size)');
        assert('is_null($max_room_size) | is_int($max_room_size)');
        assert('is_null($room_count) | is_int($room_count)');

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
        $id,
        $mail_service_list = "",
        $mail_room_setup = "",
        $days_send_service = null,
        $days_send_room_setup = null,
        $mail_material_list = "",
        $days_send_material_list = null,
        $mail_accomodation_list = "",
        $days_send_accomodation_list = null,
        $days_remind_accomodation_list = null
    ) {
        assert('is_int($id)');
        assert('is_string($mail_service_list)');
        assert('is_string($mail_room_setup)');
        assert('is_int($days_send_service) || is_null($days_send_service)');
        assert('is_int($days_send_room_setup) || is_null($days_send_room_setup)');
        assert('is_string($mail_material_list)');
        assert('is_int($days_send_material_list) || is_null($days_send_material_list)');
        assert('is_string($mail_accomodation_list)');
        assert('is_int($days_send_accomodation_list) || is_null($days_send_accomodation_list)');
        assert('is_int($days_remind_accomodation_list) || is_null($days_remind_accomodation_list)');


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
        $id,
        $fixed_rate_day = null,
        $fixed_rate_all_inclusive = null,
        $bed_and_breakfast = null,
        $bed = null,
        $fixed_rate_conference = null,
        $room_usage = null,
        $other = null,
        $terms = ""
    ) {
        assert('is_int($id)');
        assert('is_float($fixed_rate_day) || is_null($fixed_rate_day)');
        assert('is_float($fixed_rate_all_inclusive) || is_null($fixed_rate_all_inclusive)');
        assert('is_float($bed_and_breakfast) || is_null($bed_and_breakfast)');
        assert('is_float($bed) || is_null($bed)');
        assert('is_float($fixed_rate_conference) || is_null($fixed_rate_conference)');
        assert('is_float($room_usage) || is_null($room_usage)');
        assert('is_float($other) || is_null($other)');
        assert('is_string($terms) || is_null($terms)');

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

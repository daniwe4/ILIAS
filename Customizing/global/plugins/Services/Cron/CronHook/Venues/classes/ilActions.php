<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace CaT\Plugins\Venues;

/**
 * Communication class between front- and backend.
 * E.g. GUI only use this class to get information from ILIAS DB.
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilActions
{
    /**
     * @var \CaT\Plugins\Venues\Venues\DB
     */
    protected $venue_db;

    /**
     * @var Venues\General\DB
     */
    protected $general_db;

    /**
     * @var Venues\Address\DB
     */
    protected $address_db;

    /**
     * @var Venues\Conditions\DB
     */
    protected $conditions_db;

    /**
     * @var Venues\Contact\DB
     */
    protected $contact_db;

    /**
     * @var Venues\Rating\DB
     */
    protected $rating_db;

    /**
     * @var Venues\Service\DB
     */
    protected $service_db;

    /**
     * @var Venues\Costs\DB
     */
    protected $costs_db;

    /**
     * @var \ilVenuesPlugin
     */
    protected $plugin_object;

    /**
     * @var Tags\Venue\DB
     */
    protected $tags_db;

    /**
     * @var VenueAssignment\DB
     */
    protected $assign_db;

    /**
     * @var Tags\Search\DB
     */
    protected $search_tags_db;


    public function __construct(
        \ilVenuesPlugin $plugin_object,
        Venues\DB $venue_db,
        Venues\General\DB $general_db,
        Venues\Address\DB $address_db,
        Venues\Conditions\DB $conditions_db,
        Venues\Contact\DB $contact_db,
        Venues\Rating\DB $rating_db,
        Venues\Capacity\DB $capacity_db,
        Venues\Service\DB $service_db,
        Venues\Costs\DB $costs_db,
        Tags\Venue\DB $tags_db,
        VenueAssignment\DB $assign_db,
        $app_event_handler,
        Tags\Search\DB $search_tags_db
    ) {
        $this->plugin_object = $plugin_object;
        $this->venue_db = $venue_db;
        $this->general_db = $general_db;
        $this->address_db = $address_db;
        $this->conditions_db = $conditions_db;
        $this->contact_db = $contact_db;
        $this->rating_db = $rating_db;
        $this->tags_db = $tags_db;
        $this->assign_db = $assign_db;
        $this->capacity_db = $capacity_db;
        $this->service_db = $service_db;
        $this->costs_db = $costs_db;
        $this->app_event_handler = $app_event_handler;
        $this->search_tags_db = $search_tags_db;
    }

    /**
     * Get the plugin object
     *
     * @return \ilVenuesPlugin
     */
    public function getPlugin()
    {
        if ($this->plugin_object === null) {
            throw new \LogicException(__METHOD__ . " now plugin object defined");
        }

        return $this->plugin_object;
    }

    /**********
     * Venue
     *********/
    /**
     * Get venue by id
     *
     * @param int 	$id
     *
     * @return Venues\Venue
     */
    public function getVenue($id)
    {
        return $this->venue_db->getVenue($id);
    }

    /**
     * Get all venues
     *
     * @param string 	$order_column
     * @param string 	$order_direction
     * @param int[] | [] $filtered_tags
     *
     * @return Venues\Venue[]
     */
    public function getAllVenues($order_column, $order_direction, $filtered_tags)
    {
        return $this->venue_db->getAllVenues($order_column, $order_direction, $filtered_tags);
    }

    /**
     * Create a general configuration
     *
     * @param int 	$id
     * @param string 	$name
     * @param string 	$homepage
     * @param Tags[] | [] 	$tags
     *
     * @return General
     */
    public function createGeneralObject($id, $name, $homepage, $tags)
    {
        $general = $this->general_db->create($id, $name, $homepage, $tags);
        $this->tags_db->allocateTags($general->getId(), $general->getTags());
        $this->search_tags_db->allocateTags($general->getId(), $general->getSearchTags());
    }

    /**
     * Create a rating configuration
     *
     * @param int 	$id
     * @param float 	$rating
     * @param string 	$info
     *
     * @return Rating
     */
    public function createRatingObject($id, $rating, $info)
    {
        $this->rating_db->create($id, $rating, $info);
    }

    /**
     * Create a address configuration
     *
     * @param int 	$id
     * @param string 	$address1
     * @param string 	$country
     * @param string 	$address2
     * @param string 	$postcode
     * @param string 	$max_room_size
     * @param float 	$latitude
     * @param float 	$longitude
     * @param int 	$zoom
     *
     * @return Address
     */
    public function createAddressObject($id, $assress1, $country, $assress2, $postcode, $city, $latitude, $longitude, $zoom)
    {
        $this->address_db->create($id, $assress1, $country, $assress2, $postcode, $city, $latitude, $longitude, $zoom);
    }

    /**
     * Create a contact configuration
     *
     * @param int 	$id
     * @param string 	$internal_contact
     * @param string 	$contact
     * @param string 	$phone
     * @param string 	$fax
     * @param string 	$email
     *
     * @return Contact
     */
    public function createContactObject($id, $internal_contact, $contact, $phone, $fax, $email)
    {
        $this->contact_db->create($id, $internal_contact, $contact, $phone, $fax, $email);
    }

    /**
     * Create a conditions configuration
     *
     * @param int 	$id
     * @param bool 	$general_agreement
     * @param string 	$terms
     * @param string 	$valuta
     *
     * @return Conditions
     */
    public function createConditionsObject($id, $general_agreement, $terms, $valuta)
    {
        $this->conditions_db->create($id, $general_agreement, $terms, $valuta);
    }

    /**
     * Create a capacity configuration
     *
     * @param int 	$id
     * @param int | null	$number_rooms
     * @param int | null	$min_person_any_room
     * @param int | null	$max_person_any_room
     * @param int | null	$min_room_size
     * @param int | null	$max_room_size
     * @param int | null	$room_count
     */
    public function createCapacityObject(
        $id,
        $number_rooms = null,
        $min_person_any_room = null,
        $max_person_any_room = null,
        $min_room_size = null,
        $max_room_size = null,
        $room_count = null
    ) {
        $this->capacity_db->create($id, $number_rooms, $min_person_any_room, $max_person_any_room, $min_room_size, $max_room_size, $room_count);
    }

    /**
     * Create a service configuration
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
     */
    public function createServiceObject(
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
        $this->service_db->create(
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
    public function createCostsObject(
        int $id,
        ?float $fixed_rate_day = null,
        ?float $fixed_rate_all_inclusive = null,
        ?float $bed_and_breakfast = null,
        ?float $bed = null,
        ?float $fixed_rate_conference = null,
        ?float $room_usage = null,
        ?float $other = null,
        ?string $terms = ""
    ) {
        $this->costs_db->create(
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

    /**
     * Create a general configuration
     *
     * @param int 	$id
     * @param string 	$name
     * @param string 	$homepage
     * @param Tags[] | [] 	$tags
     * @param Tags[] | [] 	$search_tags
     *
     * @return General
     */
    public function getGeneralObject($id, $name, $homepage, $tags, $search_tags)
    {
        return $this->general_db->getGeneralObject($id, $name, $homepage, $tags, $search_tags);
    }

    /**
     * Create a rating configuration
     *
     * @param int 	$id
     * @param float 	$rating
     * @param string 	$info
     *
     * @return Rating
     */
    public function getRatingObject($id, $rating, $info)
    {
        return $this->rating_db->getRatingObject($id, $rating, $info);
    }

    /**
     * Create a address configuration
     *
     * @param int 	$id
     * @param string 	$address1
     * @param string 	$country
     * @param string 	$address2
     * @param string 	$postcode
     * @param string 	$max_room_size
     * @param float 	$latitude
     * @param float 	$longitude
     * @param int 	$zoom
     *
     * @return Address
     */
    public function getAddressObject($id, $assress1, $country, $assress2, $postcode, $city, $latitude, $longitude, $zoom)
    {
        return $this->address_db->getAddressObject($id, $assress1, $country, $assress2, $postcode, $city, $latitude, $longitude, $zoom);
    }

    /**
     * Create a contact configuration
     *
     * @param int 	$id
     * @param string 	$internal_contact
     * @param string 	$contact
     * @param string 	$phone
     * @param string 	$fax
     * @param string 	$email
     *
     * @return Contact
     */
    public function getContactObject($id, $internal_contact, $contact, $phone, $fax, $email)
    {
        return $this->contact_db->getContactObject($id, $internal_contact, $contact, $phone, $fax, $email);
    }

    /**
     * Create a conditions configuration
     *
     * @param int 	$id
     * @param bool 	$general_agreement
     * @param string 	$terms
     * @param string 	$valuta
     *
     * @return Conditions
     */
    public function getConditionsObject($id, $general_agreement, $terms, $valuta)
    {
        return $this->conditions_db->getConditionsObject($id, $general_agreement, $terms, $valuta);
    }

    /**
     * Create a capacity configuration
     *
     * @param int 	$id
     * @param int | null	$number_rooms
     * @param int | null	$min_person_any_room
     * @param int | null	$max_person_any_room
     * @param int | null	$min_room_size
     * @param int | null	$max_room_size
     * @param int | null	$room_count
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
        return $this->capacity_db->getCapacityObject($id, $number_rooms, $min_person_any_room, $max_person_any_room, $min_room_size, $max_room_size, $room_count);
    }

    /**
     * Create a service configuration
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
        return $this->service_db->getServiceObject(
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
        ?string $terms = ""
    ) {
        return $this->costs_db->getCostsObject(
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

    /**
     * Update the edited venue
     *
     * @param Venues\General\General 	$general
     * @param Venues\Rating\Rating 	$rating
     * @param Venues\Address\Address 	$address
     * @param Venues\Contact\Contact 	$contact
     * @param Venues\Conditions\Conditions 	$condition
     * @param Venues\Capacity\Capacity 	$capacity
     * @param Venues\Service\Service $service
     * @param Venues\Costs\Costs $costs
     */
    public function update(
        Venues\General\General $general,
        Venues\Rating\Rating $rating,
        Venues\Address\Address $address,
        Venues\Contact\Contact $contact,
        Venues\Conditions\Conditions $condition,
        Venues\Capacity\Capacity $capacity,
        Venues\Service\Service $service,
        Venues\Costs\Costs $costs
    ) {
        $this->general_db->update($general);
        $this->address_db->update($address);
        $this->conditions_db->update($condition);
        $this->contact_db->update($contact);
        $this->rating_db->update($rating);
        $this->capacity_db->update($capacity);
        $this->service_db->update($service);
        $this->costs_db->update($costs);

        $this->tags_db->deleteAllocationByVenueId($general->getId());
        $this->tags_db->allocateTags($general->getId(), $general->getTags());
        $this->search_tags_db->deleteAllocationByVenueId($general->getId());
        $this->search_tags_db->allocateTags($general->getId(), $general->getSearchTags());
    }

    /**
     * Remove venue by id
     *
     * @param int 		$id
     */
    public function removeVenueBy($id)
    {
        $this->general_db->delete($id);
        $this->address_db->delete($id);
        $this->conditions_db->delete($id);
        $this->contact_db->delete($id);
        $this->rating_db->delete($id);
        $this->capacity_db->delete($id);
        $this->service_db->delete($id);
        $this->costs_db->delete($id);
    }

    /**
     * Get venue options
     *
     * @return array<int, mixed>
     */
    public function getVenueOptions()
    {
        return $this->venue_db->getVenueOptions();
    }

    /**
     * Get venue name is existing
     *
     * @param string 	$new_provider_name
     *
     * @return bool
     */
    public function venueNameExist(string $new_provider_name)
    {
        return $this->venue_db->nameExists($new_provider_name);
    }

    public function getNewVenueId()
    {
        return $this->venue_db->getNextId();
    }

    /**********
     * Assignments
     *********/
    /**
     * Create a new assignment
     *
     * @param int 		$crs_id
     * @param int 		$venue_id
     */
    public function createListVenueAssignment(int $crs_id, int $venue_id, string $add_info = null)
    {
        $assignment = $this->assign_db->createListVenueAssignment($crs_id, $venue_id, $add_info);
    }

    /**
     * Create a new assignment
     *
     * @param int 		$crs_id
     * @param string 	$text
     */
    public function createCustomVenueAssignment($crs_id, $text)
    {
        $assignment = $this->assign_db->createCustomVenueAssignment($crs_id, $text);
    }

    /**
     * update an exisiting assignment
     *
     * @param VenueAssignment 		$venue_assignment
     */
    public function updateAssignment(VenueAssignment\VenueAssignment $venue_assignment)
    {
        $assignment = $this->assign_db->update($venue_assignment);
    }

    /**
     * Remove all venue assignments for course
     *
     * @param int 	$crs_id
     */
    public function removeAssignment($crs_id)
    {
        $this->assign_db->delete($crs_id);
    }

    /**
     * get assignment for course id
     *
     * @param int 	$crs_id
     *
     * @return VenueAssignment\VenueAssignment | false
     */
    public function getAssignment($crs_id)
    {
        return $this->assign_db->select($crs_id);
    }

    /**
     * Return all crs obj ids where venue is used
     *
     * @param int 	$id
     *
     * @return int[]
     */
    public function getAffectedCrsObjIds($id)
    {
        return $this->assign_db->getAffectedCrsObjIds($id);
    }

    /**
     * Throws an event after venue is updated or deleted
     *
     * @param int[] 	$crs_ids
     *
     * @return void
     */
    public function throwEvent($crs_ids)
    {
        $e = array();
        $e["crs_obj_ids"] = $crs_ids;
        $this->app_event_handler->raise("Plugin/Venue", "updateVenueStatic", $e);
    }

    /**
     * Checks venue is used in any course or accomodation
     *
     * @param int 	$id
     *
     * @return bool
     */
    public function isUsed($id)
    {
        return $this->assign_db->isAssigned($id);
    }
}

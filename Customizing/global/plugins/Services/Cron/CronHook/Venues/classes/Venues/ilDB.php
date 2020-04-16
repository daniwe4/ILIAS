<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues;

use \CaT\Plugins\Venues\Tags\Tag;
use \CaT\Plugins\Venues\ObjectFactory;
use \CaT\Plugins\Venues\Helper;

/**
 * Implementation of venue database interface
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilDB implements DB
{
    use ObjectFactory;
    use Helper;

    const TABLE_NAME = "venues_venue";
    const TABLE_TAGS = "venues_tags";
    const TABLE_TAGS_ALLOCATION = "venues_tags_venue";

    const TABLE_SEARCH_TAGS = "search_tags";
    const TABLE_SEARCH_TAGS_ALLOCATION = "search_tags_venue";

    const TABLE_GENERAL = "venues_general";
    const TABLE_RATING = "venues_rating";
    const TABLE_ADDRESS = "venues_address";
    const TABLE_CONTACT = "venues_contact";
    const TABLE_CONDITIONS = "venues_conditions";
    const TABLE_CAPACITY = "venues_capacity";
    const TABLE_SERVICE = "venues_service";
    const TABLE_COSTS = "venues_costs";

    const TAG_DELIMITER = "#:#";
    const TAGS_DELIMITER = "#|#";

    /**
     * @var \ilDBInterface
     */
    protected $db = null;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function getVenue(int $id) : Venue
    {
        $where = " WHERE A.id = " . $this->db->quote($id, "integer") . "\n";

        $query = $this->getQueryBase();
        $query .= $where;
        $query .= " GROUP BY A.id";

        $res = $this->db->query($query);

        if ($this->db->numRows($res) == 0) {
            throw new \Exception(__METHOD__ . " no venue found for id " . $id);
        }

        $row = $this->db->fetchAssoc($res);

        return $this->getVenueFromRow($row);
    }

    /**
     * @inheritdoc
     */
    public function getAllVenues(string $order_column, string $order_direction, array $filtered_tags = null) : array
    {
        $where = "";
        if (!is_null($filtered_tags) && count($filtered_tags) > 0) {
            $where = " WHERE " . $this->db->in("tags.id", $filtered_tags, false, "integer") . "\n";
        }

        $query = $this->getQueryBase();
        $query .= $where;
        $query .= " GROUP BY A.id\n";

        if ($order_column) {
            $query .= " ORDER BY " . $order_column . " " . $order_direction;
        }

        $res = $this->db->query($query);

        $ret = array();
        while ($row = $this->db->fetchAssoc($res)) {
            $ret[] = $this->getVenueFromRow($row);
        }

        return $ret;
    }

    protected function getQueryBase() : string
    {
        $query = "SELECT A.id, A.name, A.homepage" . PHP_EOL
            . "    , B.rating, B.info" . PHP_EOL
            . "    , C.address1, C.address2, C.country, C.postcode, C.city, C.latitude, C.longitude, C.zoom" . PHP_EOL
            . "    , D.internal_contact, D.contact, D.phone, D.fax, D.email" . PHP_EOL
            . "    , E.general_agreement, E.terms, E.valuta" . PHP_EOL
            . "    , F.number_rooms_overnight, F.min_person_any_room, F.max_person_any_room, F.min_room_size, F.max_room_size, F.room_count" . PHP_EOL
            . "    , G.mail_service_list, G.mail_room_setup, G.days_send_service, G.days_send_room_setup, G.mail_material_list, G.days_send_material_list" . PHP_EOL
            . "    , G.mail_accomodation_list, G.days_send_accomodation_list, G.days_remind_acco_list" . PHP_EOL
            . "    , H.fixed_rate_day, H.fixed_rate_all_inclusive, H.bed_and_breakfast, H.bed, H.fixed_rate_conference, H.room_usage, H.other, H.terms AS costs_terms" . PHP_EOL
            . "    , GROUP_CONCAT(DISTINCT CONCAT_WS('" . self::TAG_DELIMITER . "', tags.id, tags.name, tags.color) SEPARATOR '" . self::TAGS_DELIMITER . "') as tags" . PHP_EOL
            . "    , GROUP_CONCAT(DISTINCT CONCAT_WS('" . self::TAG_DELIMITER . "', search_tags.id, search_tags.name, search_tags.color) SEPARATOR '" . self::TAGS_DELIMITER . "') as search_tags" . PHP_EOL
            . " FROM " . self::TABLE_GENERAL . " A" . PHP_EOL
            . " LEFT JOIN " . self::TABLE_RATING . " B" . PHP_EOL
            . "    ON B.id = A.id" . PHP_EOL
            . " LEFT JOIN " . self::TABLE_ADDRESS . " C" . PHP_EOL
            . "    ON C.id = A.id" . PHP_EOL
            . " LEFT JOIN " . self::TABLE_CONTACT . " D" . PHP_EOL
            . "    ON D.id = A.id" . PHP_EOL
            . " LEFT JOIN " . self::TABLE_CONDITIONS . " E" . PHP_EOL
            . "    ON E.id = A.id" . PHP_EOL
            . " LEFT JOIN " . self::TABLE_CAPACITY . " F" . PHP_EOL
            . "    ON F.id = A.id" . PHP_EOL
            . " LEFT JOIN " . self::TABLE_SERVICE . " G" . PHP_EOL
            . "    ON G.id = A.id" . PHP_EOL
            . " LEFT JOIN " . self::TABLE_COSTS . " H" . PHP_EOL
            . "    ON H.id = A.id" . PHP_EOL
            . " LEFT JOIN " . self::TABLE_TAGS_ALLOCATION . " talloc" . PHP_EOL
            . "    ON talloc.venue_id = A.id" . PHP_EOL
            . " LEFT JOIN " . self::TABLE_TAGS . " tags" . PHP_EOL
            . "    ON tags.id = talloc.id" . PHP_EOL
            . " LEFT JOIN " . self::TABLE_SEARCH_TAGS_ALLOCATION . " search_talloc" . PHP_EOL
            . "    ON search_talloc.venue_id = A.id" . PHP_EOL
            . " LEFT JOIN " . self::TABLE_SEARCH_TAGS . " search_tags" . PHP_EOL
            . "    ON search_tags.id = search_talloc.id" . PHP_EOL
        ;

        return $query;
    }

    /**
     * @param string[] 	$row
     */
    protected function getVenueFromRow(array $row) : Venue
    {
        $id = (int) $row["id"];

        $general = $this->getGeneralObject(
                $id,
                $row["name"],
                $this->getDefaultOnNull($row["homepage"], ""),
                $this->createTagsFrom($row["tags"]),
                $this->createTagsFrom($row["search_tags"])
            );

        $rating = $this->getRatingObject(
                $id,
                (float) $this->getDefaultOnNull($row["rating"], 0.0),
                $this->getDefaultOnNull($row["info"], "")
            );
        $address = $this->getAddressObject(
                $id,
                $this->getDefaultOnNull($row["address1"], ""),
                $this->getDefaultOnNull($row["country"], ""),
                $this->getDefaultOnNull($row["address2"], ""),
                $this->getDefaultOnNull($row["postcode"], ""),
                $this->getDefaultOnNull($row["city"], ""),
                (float) $this->getDefaultOnNull($row["latitude"], 0.0),
                (float) $this->getDefaultOnNull($row["longitude"], 0.0),
                (int) $this->getDefaultOnNull($row["zoom"], 10)
            );
        $contact = $this->getContactObject(
                $id,
                $this->getDefaultOnNull($row["internal_contact"], ""),
                $this->getDefaultOnNull($row["contact"], ""),
                $this->getDefaultOnNull($row["phone"], ""),
                $this->getDefaultOnNull($row["fax"], ""),
                $this->getDefaultOnNull($row["email"], "")
            );
        $condition = $this->getConditionsObject(
                $id,
                (bool) $this->getDefaultOnNull($row["general_agreement"], ""),
                $this->getDefaultOnNull($row["terms"], ""),
                $this->getDefaultOnNull($row["valuta"], "")
            );

        $number_rooms_overnight = $row["number_rooms_overnight"];
        if ($number_rooms_overnight !== null) {
            $number_rooms_overnight = (int) $number_rooms_overnight;
        }

        $min_person_any_room = $row["min_person_any_room"];
        if ($min_person_any_room !== null) {
            $min_person_any_room = (int) $min_person_any_room;
        }

        $max_person_any_room = $row["max_person_any_room"];
        if ($max_person_any_room !== null) {
            $max_person_any_room = (int) $max_person_any_room;
        }

        $min_room_size = $row["min_room_size"];
        if ($min_room_size !== null) {
            $min_room_size = (int) $min_room_size;
        }

        $max_room_size = $row["max_room_size"];
        if ($max_room_size !== null) {
            $max_room_size = (int) $max_room_size;
        }

        $room_count = $row["room_count"];
        if ($room_count !== null) {
            $room_count = (int) $room_count;
        }

        $capacity = $this->getCapacityObject(
                $id,
                $number_rooms_overnight,
                $min_person_any_room,
                $max_person_any_room,
                $min_room_size,
                $max_room_size,
                $room_count
            );

        $days_send_service = $row["days_send_service"];
        if ($days_send_service !== null) {
            $days_send_service = (int) $days_send_service;
        }

        $days_send_room_setup = $row["days_send_room_setup"];
        if ($days_send_room_setup !== null) {
            $days_send_room_setup = (int) $days_send_room_setup;
        }

        $days_send_material_list = $row["days_send_material_list"];
        if ($days_send_material_list !== null) {
            $days_send_material_list = (int) $days_send_material_list;
        }

        $days_send_accomodation_list = $row["days_send_accomodation_list"];
        if ($days_send_accomodation_list !== null) {
            $days_send_accomodation_list = (int) $days_send_accomodation_list;
        }

        $days_remind_accomodation_list = $row["days_remind_acco_list"];
        if ($days_remind_accomodation_list !== null) {
            $days_remind_accomodation_list = (int) $days_remind_accomodation_list;
        }

        $service = $this->getServiceObject(
                $id,
                $this->getDefaultOnNull($row["mail_service_list"], ""),
                $this->getDefaultOnNull($row["mail_room_setup"], ""),
                $days_send_service,
                $days_send_room_setup,
                $this->getDefaultOnNull($row["mail_material_list"], ""),
                $days_send_material_list,
                $this->getDefaultOnNull($row["mail_accomodation_list"], ""),
                $days_send_accomodation_list,
                $days_remind_accomodation_list
            );

        $fixed_rate_day = $row["fixed_rate_day"];
        if ($fixed_rate_day !== null) {
            $fixed_rate_day = (float) $fixed_rate_day;
        }

        $fixed_rate_all_inclusive = $row["fixed_rate_all_inclusive"];
        if ($fixed_rate_all_inclusive !== null) {
            $fixed_rate_all_inclusive = (float) $fixed_rate_all_inclusive;
        }

        $bed_and_breakfast = $row["bed_and_breakfast"];
        if ($bed_and_breakfast !== null) {
            $bed_and_breakfast = (float) $bed_and_breakfast;
        }

        $bed = $row["bed"];
        if ($bed !== null) {
            $bed = (float) $bed;
        }

        $fixed_rate_conference = $row["fixed_rate_conference"];
        if ($fixed_rate_conference !== null) {
            $fixed_rate_conference = (float) $fixed_rate_conference;
        }

        $room_usage = $row["room_usage"];
        if ($room_usage !== null) {
            $room_usage = (float) $room_usage;
        }

        $other = $row["other"];
        if ($other !== null) {
            $other = (float) $other;
        }

        $costs = $this->getCostsObject(
                $id,
                $fixed_rate_day,
                $fixed_rate_all_inclusive,
                $bed_and_breakfast,
                $bed,
                $fixed_rate_conference,
                $room_usage,
                $other,
                $row["costs_terms"]
            );

        return new Venue(
                $id,
                $general,
                $rating,
                $address,
                $contact,
                $condition,
                $capacity,
                $service,
                $costs
            );
    }

    public function nameExists(string $name) : bool
    {
        $query =
             "SELECT name" . PHP_EOL
            . " FROM " . self::TABLE_GENERAL . PHP_EOL
        ;

        $res = $this->db->query($query);

        while ($row = $this->db->fetchAssoc($res)) {
            if (strcmp($name, $row['name']) == 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, mixed>
     */
    public function getVenueOptions() : array
    {
        $query = "SELECT id, name\n"
                . " FROM " . self::TABLE_GENERAL . "\n";

        $res = $this->db->query($query);
        $ret = array();
        while ($row = $this->db->fetchAssoc($res)) {
            $ret[$row["id"]] = $row["name"];
        }

        return $ret;
    }

    /**
     * @return Tag[] | []
     */
    protected function createTagsFrom(string $tag_string) : array
    {
        $tags = array();

        if ($tag_string !== null && $tag_string != "") {
            foreach (explode(self::TAGS_DELIMITER, $tag_string) as $tag) {
                $tag_vals = explode(self::TAG_DELIMITER, $tag);
                $tags[] = new Tag((int) $tag_vals[0], $tag_vals[1], $tag_vals[2]);
            }
        }

        return $tags;
    }

    public function getVenueNameById(int $id) : string
    {
        $query = "SELECT name\n"
                . " FROM " . self::TABLE_GENERAL . "\n"
                . " WHERE id = " . $this->db->quote($id, "integer");

        $res = $this->db->query($query);
        $row = $this->db->fetchAssoc($res);

        return $row["name"];
    }

    public function getNextId() : int
    {
        return (int) $this->db->nextId(self::TABLE_NAME);
    }

    public function createSequence()
    {
        $this->db->createSequence(self::TABLE_NAME);
    }
}

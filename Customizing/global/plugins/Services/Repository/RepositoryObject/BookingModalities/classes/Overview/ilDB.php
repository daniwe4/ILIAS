<?php
namespace CaT\Plugins\BookingModalities\Overview;

/**
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class ilDB implements DB
{
    const TABLE_HHD_USRCRS = "hhd_usrcrs";
    const TABLE_USR_DATA = "usr_data";
    const TABLE_UDF_DEFINITION = "udf_definition";
    const TABLE_UDF_TEXT = "udf_text";

    const BOOKING_STATUS_PARTICIPANT = 'participant';
    const BOOKING_STATUS_CANCELLED = 'cancelled';
    const BOOKING_STATUS_WAITING_CANCELLED = 'waiting_cancelled';
    const BOOKING_STATUS_WAITING_SELF_CANCELLED = 'waiting_self_cancelled';
    const BOOKING_STATUS_CANCELLED_AFTER_DEADLINE = 'cancelled_after_deadline';
    const BOOKING_STATUS_WAITING = 'waiting';

    const F_USR_ID = "usr_id";
    const F_LASTNAME = "lastname";
    const F_FIRSTNAME = "firstname";
    const F_LOGIN = "login";
    const F_STATUS = "status";
    const F_BOOKING_DATE = "booking_date";
    const F_CANCEL_BOOKING_DATE = "cancel_booking_date";
    const F_WAITING_DATE = "waiting_date";
    const F_CANCEL_WAITING_DATE = "cancel_waiting_date";
    const F_BOOKER = "booker";
    const F_BOOKED_VIA = "booked_via";
    const F_CREATOR_FIRSTNAME = "creator_firstname";
    const F_CREATOR_LASTNAME = "creator_lastname";

    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function getBookings(int $crs_id, $order_field, $order_direction, $limit, $offset, $selected_columns)
    {
        $need_org_units = false;

        $additional_select = "";
        $additional_join = "";

        foreach ($selected_columns as $field) {
            if ($field == "org_units") {
                $need_org_units = true;
                continue;
            }

            if ($this->isUdfField($field)) {
                $id = $this->getUdfId($field);

                $additional_join .=
                     "LEFT JOIN " . self::TABLE_UDF_TEXT . " " . $field
                    . " ON u.usr_id = " . $field . ".usr_id AND " . $field . ".field_id = " . $id . PHP_EOL;
                $additional_select .= ", " . $field . ".value AS " . $field . PHP_EOL;
            } else {
                $additional_select .= ", u." . $field . " AS " . $field . PHP_EOL;
            }
        }

        $query =
             "SELECT" . PHP_EOL
            . "    uc.usr_id," . PHP_EOL
            . "    uc.booking_status as status," . PHP_EOL
            . "    uc.booking_date," . PHP_EOL
            . "    uc.cancel_booking_date," . PHP_EOL
            . "    uc.waiting_date," . PHP_EOL
            . "    uc.cancel_waiting_date," . PHP_EOL
            . "    c.login AS booker," . PHP_EOL
            . "    u.lastname," . PHP_EOL
            . "    u.firstname," . PHP_EOL
            . "    u.login" . PHP_EOL
             . $additional_select
            . "FROM " . self::TABLE_HHD_USRCRS . " uc" . PHP_EOL
            . "JOIN " . self::TABLE_USR_DATA . " u" . PHP_EOL
            . "    ON uc.usr_id = u.usr_id" . PHP_EOL
            . "JOIN " . self::TABLE_USR_DATA . " c" . PHP_EOL
            . "    ON uc.creator = c.usr_id" . PHP_EOL
             . $additional_join
            . "WHERE uc.crs_id = " . $this->db->quote($crs_id, "integer") . PHP_EOL
            . "    AND (uc.booking_status = '" . self::BOOKING_STATUS_PARTICIPANT . "'" . PHP_EOL
            . "        OR uc.booking_status = '" . self::BOOKING_STATUS_WAITING . "')" . PHP_EOL
            . "    AND uc.booking_status != 'null'" . PHP_EOL
            . "ORDER BY " . $order_field . " " . $order_direction . PHP_EOL
            . "LIMIT " . $this->db->quote($limit, "integer") . PHP_EOL
            . "OFFSET " . $this->db->quote($offset, "integer") . PHP_EOL
        ;

        $result = $this->db->query($query);

        if ($this->db->numRows($result) == 0) {
            return array();
        }

        return $this->getOverviewObjects($result, $selected_columns, $need_org_units);
    }

    protected function isUdfField(string $field) : bool
    {
        return strpos($field, "udf_") === 0;
    }

    private function getUdfId($field)
    {
        return (int) (explode("_", $field))[1];
    }

    /**
     * @inheritdoc
     */
    public function getMaxBookings(int $crs_id)
    {

        $query =
             "SELECT" . PHP_EOL
            . "    uc.usr_id" . PHP_EOL
            . "FROM " . self::TABLE_HHD_USRCRS . " uc" . PHP_EOL
            . "JOIN " . self::TABLE_USR_DATA . " u" . PHP_EOL
            . "    ON uc.usr_id = u.usr_id" . PHP_EOL
            . "JOIN " . self::TABLE_USR_DATA . " c" . PHP_EOL
            . "    ON uc.creator = c.usr_id" . PHP_EOL
            . "WHERE uc.crs_id = " . $this->db->quote($crs_id, "integer") . PHP_EOL
            . "    AND (uc.booking_status = '" . self::BOOKING_STATUS_PARTICIPANT . "'" . PHP_EOL
            . "        OR uc.booking_status = '" . self::BOOKING_STATUS_WAITING . "')" . PHP_EOL
            . "    AND uc.booking_status != 'null'" . PHP_EOL
        ;
        $result = $this->db->query($query);

        return $this->db->numRows($result);
    }

    /**
     * @inheritdoc
     */
    public function getCancellations(int $crs_id, $order_field, $order_direction, $limit, $offset, $selected_columns)
    {
        $need_org_units = false;

        $additional_select = "";
        $additional_join = "";

        foreach ($selected_columns as $field) {
            if ($field == "org_units") {
                $need_org_units = true;
                continue;
            }

            if ($this->isUdfField($field)) {
                $id = $this->getUdfId($field);
                $additional_join .=
                    "LEFT JOIN " . self::TABLE_UDF_TEXT . " " . $field
                    . " ON u.usr_id = " . $field . ".usr_id AND " . $field . ".field_id = " . $id . PHP_EOL;
                $additional_select .= ", " . $field . ".value AS " . $field . PHP_EOL;
            } else {
                $additional_select .= ", u." . $field . " AS " . $field . PHP_EOL;
            }
        }
        $query =
             "SELECT" . PHP_EOL
            . "    uc.usr_id," . PHP_EOL
            . "    uc.booking_status AS status," . PHP_EOL
            . "    uc.booking_date," . PHP_EOL
            . "    uc.cancel_booking_date," . PHP_EOL
            . "    uc.waiting_date," . PHP_EOL
            . "    uc.cancel_waiting_date," . PHP_EOL
            . "    c.login AS booker," . PHP_EOL
            . "    u.lastname," . PHP_EOL
            . "    u.firstname," . PHP_EOL
            . "    u.login" . PHP_EOL
            . $additional_select
            . "FROM " . self::TABLE_HHD_USRCRS . " uc" . PHP_EOL
            . "JOIN " . self::TABLE_USR_DATA . " u" . PHP_EOL
            . "    ON uc.usr_id = u.usr_id" . PHP_EOL
            . "JOIN " . self::TABLE_USR_DATA . " c" . PHP_EOL
            . "    ON uc.creator = c.usr_id" . PHP_EOL
            . $additional_join
            . "WHERE uc.crs_id = " . $this->db->quote($crs_id, "integer") . PHP_EOL
            . "    AND" . PHP_EOL
            . "    (" . PHP_EOL
            . "       uc.booking_status = '" . self::BOOKING_STATUS_CANCELLED . "'" . PHP_EOL
            . "    OR uc.booking_status = '" . self::BOOKING_STATUS_WAITING_CANCELLED . "'" . PHP_EOL
            . "    OR uc.booking_status = '" . self::BOOKING_STATUS_WAITING_SELF_CANCELLED . "'" . PHP_EOL
            . "    OR uc.booking_status = '" . self::BOOKING_STATUS_CANCELLED_AFTER_DEADLINE . "'" . PHP_EOL
            . "    )" . PHP_EOL
            . "    AND uc.booking_status != 'null'" . PHP_EOL
            . "ORDER BY " . $order_field . " " . $order_direction . PHP_EOL
            . "LIMIT " . $this->db->quote($limit, "integer") . PHP_EOL
            . "OFFSET " . $this->db->quote($offset, "integer") . PHP_EOL
        ;

        $result = $this->db->query($query);

        if ($this->db->numRows($result) == 0) {
            return array();
        }

        return $this->getOverviewObjects($result, $selected_columns, $need_org_units);
    }

    /**
     * @inheritdoc
     */
    public function getMaxCancellations(int $crs_id)
    {
        $query =
             "SELECT" . PHP_EOL
            . "    uc.usr_id" . PHP_EOL
            . "FROM " . self::TABLE_HHD_USRCRS . " uc" . PHP_EOL
            . "JOIN " . self::TABLE_USR_DATA . " u" . PHP_EOL
            . "    ON uc.usr_id = u.usr_id" . PHP_EOL
            . "JOIN " . self::TABLE_USR_DATA . " c" . PHP_EOL
            . "    ON uc.creator = c.usr_id" . PHP_EOL
            . "WHERE uc.crs_id = " . $this->db->quote($crs_id, "integer") . PHP_EOL
            . "    AND" . PHP_EOL
            . "    (" . PHP_EOL
            . "       uc.booking_status = '" . self::BOOKING_STATUS_CANCELLED . "'" . PHP_EOL
            . "    OR uc.booking_status = '" . self::BOOKING_STATUS_WAITING_CANCELLED . "'" . PHP_EOL
            . "    OR uc.booking_status = '" . self::BOOKING_STATUS_WAITING_SELF_CANCELLED . "'" . PHP_EOL
            . "    OR uc.booking_status = '" . self::BOOKING_STATUS_CANCELLED_AFTER_DEADLINE . "'" . PHP_EOL
            . "    )" . PHP_EOL
            . "    AND uc.booking_status != 'null'" . PHP_EOL
        ;

        $result = $this->db->query($query);

        return $this->db->numRows($result);
    }

    /**
     * Transform db query results into Overview objects.
     *
     * @param  $result
     * @return Overview[]
     */
    protected function getOverviewObjects($result, $selected_columns, $need_org_units)
    {
        $overviews = array();

        while ($row = $this->db->fetchAssoc($result)) {
            $overview = new Overview();

            $additional_fields = [];
            foreach ($selected_columns as $column) {
                if (is_null($row[$column])) {
                    $row[$column] = "";
                }
                $additional_fields[$column] = $row[$column];
            }

            if ($need_org_units) {
                $additional_fields["org_units"] = $this->getOrgUnitsForUserId((int) $row[self::F_USR_ID]);
            }

            $overview = $overview
                ->withUsrId((int) $row[self::F_USR_ID])
                ->withLastname($row[self::F_LASTNAME])
                ->withFirstname($row[self::F_FIRSTNAME])
                ->withLogin($row[self::F_LOGIN])
                ->withStatus($row[self::F_STATUS])
                ->withBookingDate(new \ilDateTime($row[self::F_BOOKING_DATE], IL_CAL_DATE))
                ->withCancelBookingDate(new \ilDateTime($row[self::F_CANCEL_BOOKING_DATE], IL_CAL_DATE))
                ->withWaitingDate(new \ilDateTime($row[self::F_WAITING_DATE], IL_CAL_DATE))
                ->withCancelWaitingDate(new \ilDateTime($row[self::F_CANCEL_WAITING_DATE], IL_CAL_DATE))
                ->withBooker($row[self::F_BOOKER])
                ->withAdditionalFields($additional_fields)
            ;

            $overviews[] = $overview;
        }

        return $overviews;
    }

    protected function getOrgUnitsForUserId(int $usr_id) : string
    {
        $orgu_tree = \ilObjOrgUnitTree::_getInstance();
        $orgu_refs = $orgu_tree->getOrgUnitsOfUser($usr_id);

        $orgu_vals = [];
        foreach ($orgu_refs as $orgu_ref_id) {
            $orgu_vals[] = $orgu_tree->getOrgUnitPath($orgu_ref_id);
        }
        return implode(", ", $orgu_vals);
    }
}

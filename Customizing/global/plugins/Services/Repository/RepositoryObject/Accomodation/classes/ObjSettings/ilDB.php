<?php

declare(strict_types=1);

namespace CaT\Plugins\Accomodation\ObjSettings;

use DateTime;
use ilDBInterface;

/**
 * DB handle of settings
 *
 * @author 	Nils Haagen 	<nils.haagen@concepts-and-training.de>
 */
class ilDB implements DB
{
    const TABLE_NAME = "xoac_objects";

    /**
     * @var ilDBInterface
     */
    protected $db;

    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function create(
        int $obj_id,
        bool $dates_from_course = true,
        DateTime $start_date = null,
        DateTime $end_date = null,
        int $location_obj_id = null,
        bool $location_from_course = false,
        bool $allow_prior_day = null,
        bool $allow_following_day = null,
        int $booking_end = null,
        bool $mailing_use_venue_settings = true,
        string $mail_recipient = "",
        int $send_days_before = 0,
        int $send_reminder_days_before = 0,
        bool $edit_notes = false
    ) : ObjSettings {
        $settings = new ObjSettings(
            $obj_id,
            $dates_from_course,
            $start_date,
            $end_date,
            $location_obj_id,
            $location_from_course,
            $allow_prior_day,
            $allow_following_day,
            $booking_end,
            $mailing_use_venue_settings,
            $mail_recipient,
            $send_days_before,
            $send_reminder_days_before,
            $edit_notes
        );

        $start_date = $settings->getStartDate();
        if (!is_null($start_date)) {
            $start_date = $start_date->format("Y-m-d");
        }

        $end_date = $settings->getEndDate();
        if (!is_null($end_date)) {
            $end_date = $end_date->format("Y-m-d");
        }

        $values = array(
            "obj_id" => array("int", $settings->getObjId()),
            "dates_from_course" => array("int", $settings->getDatesByCourse()),
            "start_date" => array("text",$start_date),
            "end_date" => array("text", $end_date),
            "location_obj_id" => array("int", $settings->getLocationObjId()),
            "location_from_course" => array("int", $settings->getLocationFromCourse()),
            "allow_prior_day" => array("int", $settings->isPriorDayAllowed()),
            "allow_following_day" => array("int", $settings->isFollowingDayAllowed()),
            "booking_end" => array("int", $settings->getBookingEnd()),
            "mailsettings_from_venue" => array("int", $settings->getMailsettingsFromVenue()),
            "mail_recipient" => array("text", $settings->getMailRecipient()),
            "mail_senddaysbefore" => array("int", $settings->getSendDaysBefore()),
            "mail_reminddaysbefore" => array("int", $settings->getSendReminderDaysBefore()),
            "edit_notes" => array("text", $settings->getEditNotes())
        );

        $this->getDB()->insert(static::TABLE_NAME, $values);
        return $settings;
    }

    /**
     * @inheritdoc
     */
    public function update(ObjSettings $settings)
    {
        $where = array("obj_id" => array("int", $settings->getObjId()));

        $start_date = $settings->getStartDate();
        if (!is_null($start_date)) {
            $start_date = $start_date->format("Y-m-d");
        }
        $end_date = $settings->getEndDate();
        if (!is_null($end_date)) {
            $end_date = $end_date->format("Y-m-d");
        }

        $values = array(
            "dates_from_course" => array("int", $settings->getDatesByCourse()),
            "start_date" => array("text", $start_date),
            "end_date" => array("text", $end_date),
            "location_obj_id" => array("int", $settings->getLocationObjId()),
            "location_from_course" => array("int", $settings->getLocationFromCourse()),
            "allow_prior_day" => array("int", $settings->isPriorDayAllowed()),
            "allow_following_day" => array("int", $settings->isFollowingDayAllowed()),
            "booking_end" => array("int", $settings->getBookingEnd()),
            "mailsettings_from_venue" => array("int", $settings->getMailsettingsFromVenue()),
            "mail_recipient" => array("text", $settings->getMailRecipient()),
            "mail_senddaysbefore" => array("int", $settings->getSendDaysBefore()),
            "mail_reminddaysbefore" => array("int", $settings->getSendReminderDaysBefore()),
            "edit_notes" => array("text", $settings->getEditNotes())
        );

        $this->getDB()->update(static::TABLE_NAME, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function selectFor($obj_id)
    {
        assert('is_int($obj_id)');
        $query = "SELECT obj_id, dates_from_course, start_date, end_date," . PHP_EOL
                . " location_obj_id, location_from_course, allow_prior_day, allow_following_day, booking_end," . PHP_EOL
                . " mailsettings_from_venue, mail_recipient, mail_senddaysbefore, mail_reminddaysbefore, edit_notes" . PHP_EOL
                . " FROM " . static::TABLE_NAME . PHP_EOL
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer") . PHP_EOL;

        $res = $this->getDB()->query($query);
        if ($this->getDB()->numRows($res) == 0) {
            throw new \LogicException("Error Processing Request - " . $query, 1);
        }
        $row = $this->getDB()->fetchAssoc($res);


        $start_date = $row['start_date'];
        if (!is_null($start_date)) {
            $start_date = new DateTime($start_date);
        }
        $end_date = $row['end_date'];
        if (!is_null($end_date)) {
            $end_date = new DateTime($end_date);
        }


        $settings = new ObjSettings(
            (int) $row['obj_id'],
            (bool) $row['dates_from_course'],
            $start_date,
            $end_date,
            (int) $row['location_obj_id'],
            (bool) $row['location_from_course'],
            (bool) $row['allow_prior_day'],
            (bool) $row['allow_following_day'],
            (int) $row['booking_end'],
            (bool) $row['mailsettings_from_venue'],
            (string) $row['mail_recipient'],
            (int) $row['mail_senddaysbefore'],
            (int) $row['mail_reminddaysbefore'],
            (bool) $row['edit_notes']
        );
        return $settings;
    }

    /**
     * @inheritdoc
     */
    public function deleteFor($obj_id)
    {
        $query = "DELETE FROM " . static::TABLE_NAME . PHP_EOL
                . " WHERE obj_id = " . $this->getDB()->quote($obj_id, "integer");
        $this->getDB()->manipulate($query);
    }


    /**
     * Get intance of db
     *
     * @throws \Exception
     * @return ilDBInterface
     */
    protected function getDB()
    {
        if (!$this->db) {
            throw new \Exception("no Database");
        }
        return $this->db;
    }

    /**
     * Create table
     *
     * @return void
     */
    public function createTable()
    {
        if (!$this->getDB()->tableExists(static::TABLE_NAME)) {
            $fields =
                array(
                    'obj_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    'location_obj_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => false
                    ),
                    'location_from_course' => array(
                        'type' => 'integer',
                        'length' => 1,
                        'notnull' => false
                    ),
                    'allow_prior_day' => array(
                        'type' => 'integer',
                        'length' => 1,
                        'notnull' => false
                    ),
                    'allow_following_day' => array(
                        'type' => 'integer',
                        'length' => 1,
                        'notnull' => false
                    ),
                    'booking_end' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => false
                    )
                );

            $this->getDB()->createTable(static::TABLE_NAME, $fields);
        }
    }

    /**
     * Set primary key for table
     *
     * @return void
     */
    public function createPrimaryKey()
    {
        $this->getDB()->addPrimaryKey(static::TABLE_NAME, array("obj_id"));
    }



    /**
     * Steps up every location id with value 1
     *
     * @return void
     */
    public function stepUpLocationId()
    {
        $query = "UPDATE " . self::TABLE_NAME . PHP_EOL
                . " SET xoac_objects.location_obj_id = " . PHP_EOL
                . "    (SELECT IFNULL(MIN(venues_general.id), xoac_objects.location_obj_id)" . PHP_EOL
                . "     FROM venues_general WHERE venues_general.id > xoac_objects.location_obj_id)" . PHP_EOL
                . " WHERE xoac_objects.location_from_course = 0";

        $this->getDB()->manipulate($query);
    }


    /**
     * Update 1
     *
     * @return void
     */
    public function update1()
    {
        if (!$this->getDB()->tableColumnExists(static::TABLE_NAME, "mailsettings_from_venue")) {
            $field = array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => false
            );
            $this->getDB()->addTableColumn(static::TABLE_NAME, "mailsettings_from_venue", $field);
        }

        if (!$this->getDB()->tableColumnExists(static::TABLE_NAME, "mail_recipient")) {
            $field = array(
                'type' => 'text',
                'length' => 256,
                'notnull' => false
            );
            $this->getDB()->addTableColumn(static::TABLE_NAME, "mail_recipient", $field);
        }

        if (!$this->getDB()->tableColumnExists(static::TABLE_NAME, "mail_senddaysbefore")) {
            $field = array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => false
            );
            $this->getDB()->addTableColumn(static::TABLE_NAME, "mail_senddaysbefore", $field);
        }
    }

    /**
     * Update 2
     *
     * @return void
     */
    public function update2()
    {
        if (!$this->getDB()->tableColumnExists(static::TABLE_NAME, "mail_reminddaysbefore")) {
            $field = array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => false
            );
            $this->getDB()->addTableColumn(static::TABLE_NAME, "mail_reminddaysbefore", $field);
        }
    }
    /**
     * Update 3, insert date-columns
     *
     * @return void
     */
    public function update3()
    {
        if (!$this->getDB()->tableColumnExists(static::TABLE_NAME, "start_date")) {
            $field = array(
                'type' => 'text',
                'length' => 16,
                'notnull' => false
            );
            $this->getDB()->addTableColumn(static::TABLE_NAME, "start_date", $field);
        }

        if (!$this->getDB()->tableColumnExists(static::TABLE_NAME, "end_date")) {
            $field = array(
                'type' => 'text',
                'length' => 16,
                'notnull' => false
            );
            $this->getDB()->addTableColumn(static::TABLE_NAME, "end_date", $field);
        }
        if (!$this->getDB()->tableColumnExists(static::TABLE_NAME, "dates_from_course")) {
            $field = array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => true
            );
            $this->getDB()->addTableColumn(static::TABLE_NAME, "dates_from_course", $field);
        }
    }

    public function update4()
    {
        if (!$this->getDB()->tableColumnExists(static::TABLE_NAME, "edit_notes")) {
            $field = array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => true,
                'default' => 0
            );
            $this->getDB()->addTableColumn(static::TABLE_NAME, "edit_notes", $field);
        }
    }
}

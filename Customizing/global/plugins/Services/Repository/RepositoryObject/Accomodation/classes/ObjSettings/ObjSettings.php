<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\Accomodation\ObjSettings;

use DateTime;

/**
 * Accomodation-objects can/must be configured. This is to hold the parameters.
 */
class ObjSettings
{
    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var bool
     */
    protected $dates_from_course;

    /**
     * @var DateTime | null
     */
    protected $start_date;

    /**
     * @var DateTime | null
     */
    protected $end_date;

    /**
     * @var int
     */
    protected $location_obj_id;

    /**
     * @var bool
     */
    protected $use_location_from_course;

    /**
     * @var boolean
     */
    protected $allow_prior_day;

    /**
     * @var boolean
     */
    protected $allow_following_day;

    /**
     * @var int
     */
    protected $booking_end;

    /**
     * @var bool
     */
    protected $mailing_use_venue_settings;

    /**
     * @var string
     */
    protected $mail_recipient;

    /**
     * @var int
     */
    protected $send_days_before;

    /**
     * @var int
     */
    protected $send_reminder_days_before;

    /**
     * @var bool
     */
    protected $edit_notes;

    public function __construct(
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
    ) {
        $this->obj_id = $obj_id;
        $this->dates_from_course = $dates_from_course;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->location_obj_id = $location_obj_id;
        $this->use_location_from_course = $location_from_course;
        $this->allow_prior_day = $allow_prior_day;
        $this->allow_following_day = $allow_following_day;
        $this->booking_end = $booking_end;
        $this->mailing_use_venue_settings = $mailing_use_venue_settings;
        $this->mail_recipient = $mail_recipient;
        $this->send_days_before = $send_days_before;
        $this->send_reminder_days_before = $send_reminder_days_before;
        $this->edit_notes = $edit_notes;
    }

    /**
     * Get the object's id.
     */
    public function getObjId() : int
    {
        return $this->obj_id;
    }

    /**
     * Should the dates be defined by the course?
     */
    public function getDatesByCourse() : bool
    {
        return (bool) $this->dates_from_course;
    }

    /**
     * Should the dates be defined by the course?
     */
    public function withDatesByCourse(bool $by_course) : ObjSettings
    {
        $clone = clone $this;
        $clone->dates_from_course = $by_course;
        return $clone;
    }

    /**
     * Get the starting-date of accomodations
     *
     * @return DateTime | null
     */
    public function getStartDate()
    {
        return $this->start_date;
    }

    /**
     * Get Settings like this with start-date
     *
     * @param  DateTime | null 	$dat
     * @return ObjSettings
     */
    public function withStartDate(DateTime $dat = null) : ObjSettings
    {
        $clone = clone $this;
        $clone->start_date = $dat;
        return $clone;
    }

    /**
     * Get the end-date of accomodations
     *
     * @return DateTime | null
     */
    public function getEndDate()
    {
        return $this->end_date;
    }

    /**
     * Get Settings like this with end-date
     *
     * @param  DateTime | null 	$dat
     * @return ObjSettings
     */
    public function withEndDate(DateTime $dat = null) : ObjSettings
    {
        $clone = clone $this;
        $clone->end_date = $dat;
        return $clone;
    }
    /**
     * Get the location's obj_id.
     */
    public function getLocationObjId()
    {
        return $this->location_obj_id;
    }

    /**
     * Get Settings like this with location_obj_id
     */
    public function withLocationObjId(int $location_obj_id) : ObjSettings
    {
        $clone = clone $this;
        $clone->location_obj_id = $location_obj_id;
        return $clone;
    }

    /**
     * Should the location be retrieved from course?
     */
    public function getLocationFromCourse() : bool
    {
        return (bool) $this->use_location_from_course;
    }

    /**
     * Get Settings like this with use_location_from_course
     */
    public function withLocationFromCourse(bool $use_location_from_course) : ObjSettings
    {
        $clone = clone $this;
        $clone->use_location_from_course = $use_location_from_course;
        return $clone;
    }

    /**
     * Is a reservation allowed for the day prior to the start of the course?
     */
    public function isPriorDayAllowed() : bool
    {
        return (bool) $this->allow_prior_day;
    }

    /**
     * Get Settings like this with prior day alowed/disallowed
     */
    public function withPriorDayAllowed(bool $allow_prior_day) : ObjSettings
    {
        $clone = clone $this;
        $clone->allow_prior_day = $allow_prior_day;
        return $clone;
    }

    /**
     * Is a reservation allowed for the following day?
     */
    public function isFollowingDayAllowed() : bool
    {
        return (bool) $this->allow_following_day;
    }

    /**
     * Get Settings like this with following day alowed/disallowed
     */
    public function withFollowingDayAllowed(bool $allow_following_day) : ObjSettings
    {
        $clone = clone $this;
        $clone->allow_following_day = $allow_following_day;
        return $clone;
    }

    /**
     * Get the booking-deadline in days
     */
    public function getBookingEnd()
    {
        return $this->booking_end;
    }

    /**
     * Get Settings like this with following day alowed/disallowed
     */
    public function withBookingEnd(int $booking_end) : ObjSettings
    {
        $clone = clone $this;
        $clone->booking_end = $booking_end;
        return $clone;
    }

    /**
     * Should the mail-settings be retrieved from the configured venue?
     */
    public function withMailsettingsFromVenue(bool $use_venue_settings) : ObjSettings
    {
        $clone = clone $this;
        $clone->mailing_use_venue_settings = $use_venue_settings;
        return $clone;
    }

    /**
     * Should the mail-settings be retrieved from the configured venue?
     */
    public function getMailsettingsFromVenue() : bool
    {
        return (bool) $this->mailing_use_venue_settings;
    }

    /**
     * Set the recipient-address for accomodation-mail
     */
    public function withMailRecipient(string $recipient) : ObjSettings
    {
        $clone = clone $this;
        $clone->mail_recipient = $recipient;
        return $clone;
    }

    /**
     * Recipient-address for accomodation-mail
     */
    public function getMailRecipient() : string
    {
        return $this->mail_recipient;
    }

    /**
     * Send mail x days before training starts.
     */
    public function withSendDaysBefore(int $days) : ObjSettings
    {
        $clone = clone $this;
        $clone->send_days_before = $days;
        return $clone;
    }

    /**
     * Send mail x days before training starts.
     */
    public function getSendDaysBefore() : int
    {
        return $this->send_days_before;
    }

    /**
     * Send reminder-mail x days before training starts.
     */
    public function withSendReminderDaysBefore(int $days) : ObjSettings
    {
        $clone = clone $this;
        $clone->send_reminder_days_before = $days;
        return $clone;
    }

    /**
     * Send reminder-mail x days before training starts.
     */
    public function getSendReminderDaysBefore() : int
    {
        return $this->send_reminder_days_before;
    }

    public function getEditNotes() : bool
    {
        return (bool) $this->edit_notes;
    }

    public function withEditNotes(bool $edit_notes) : ObjSettings
    {
        $clone = clone $this;
        $clone->edit_notes = $edit_notes;
        return $clone;
    }
}

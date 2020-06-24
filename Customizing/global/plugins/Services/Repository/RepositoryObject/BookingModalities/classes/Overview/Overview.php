<?php
namespace CaT\Plugins\BookingModalities\Overview;

/**
 * Data object for a concret booking.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class Overview
{
    /**
     * @var int
     */
    protected $usr_id;

    /**
     * @var string
     */
    protected $lastname;

    /**
     * @var string
     */
    protected $firstname;

    /**
     * @var string
     */
    protected $login;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var ilDateTime
     */
    protected $booking_date;

    /**
     * @var ilDateTime
     */
    protected $cancel_booking_date;

    /**
     * @var ilDateTime
     */
    protected $waiting_date;

    /**
     * @var ilDateTime
     */
    protected $cancel_waiting_date;

    /**
     * @var string
     */
    protected $booker;

    /**
     * @var array
     */
    protected $additional_fields;

    /**
     * Get UsrId
     *
     * @return 	int
     */
    public function getUsrId()
    {
        return $this->usr_id;
    }

    /**
     * Set UsrId with $value
     *
     * @param 	int 	$value
     * @return 	$this
     */
    public function withUsrId(int $value)
    {
        $clone = clone $this;
        $clone->usr_id = $value;
        return $clone;
    }

    /**
     * Get lastname
     *
     * @return 	string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set lastname with $value
     *
     * @param 	string 	$value
     * @return 	$this
     */
    public function withLastname(string $value)
    {
        $clone = clone $this;
        $clone->lastname = $value;
        return $clone;
    }

    /**
     * Get firstname.
     *
     * @return 	string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set firstname with $value.
     *
     * @param 	string 	$value
     * @return 	$this
     */
    public function withFirstname(string $value)
    {
        $clone = clone $this;
        $clone->firstname = $value;
        return $clone;
    }

    /**
     * Get login name.
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Set login name with $value.
     *
     * @param  string $value
     * @return $this
     */
    public function withLogin(string $value)
    {
        $clone = clone $this;
        $clone->login = $value;
        return $clone;
    }

    /**
     * Get status
     *
     * @return 	string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set status with $value
     *
     * @param 	string 	$value
     * @return 	$this
     */
    public function withStatus(string $value)
    {
        $clone = clone $this;
        $clone->status = $value;
        return $clone;
    }

    /**
     * Get booking_date
     *
     * @return 	\ilDateTime | null
     */
    public function getBookingDate()
    {
        return $this->booking_date;
    }

    /**
     * Set booking_date with $value
     *
     * @param 	\ilDateTime | null 	$value
     * @return 	$this
     */
    public function withBookingDate($value)
    {
        $clone = clone $this;
        $clone->booking_date = $value;
        return $clone;
    }

    /**
     * Get cancel_booking_date
     *
     * @return 	\ilDateTime | null
     */
    public function getCancelBookingDate()
    {
        return $this->cancel_booking_date;
    }

    /**
     * Set cancel_booking_date with $value
     *
     * @param 	\ilDateTime | null 	$value
     * @return 	$this
     */
    public function withCancelBookingDate($value)
    {
        $clone = clone $this;
        $clone->cancel_booking_date = $value;
        return $clone;
    }

    /**
     * Get waiting_date
     *
     * @return 	\ilDateTime | null
     */
    public function getWaitingDate()
    {
        return $this->waiting_date;
    }

    /**
     * Set waiting_date with $value
     *
     * @param 	\ilDateTime | null 	$value
     * @return 	$this
     */
    public function withWaitingDate($value)
    {
        $clone = clone $this;
        $clone->waiting_date = $value;
        return $clone;
    }

    /**
     * Get cancel_waiting_date
     *
     * @return 	\ilDateTime | null
     */
    public function getCancelWaitingDate()
    {
        return $this->cancel_waiting_date;
    }

    /**
     * Set cancel_waiting_date with $value
     *
     * @param 	\ilDateTime | null 	$value
     * @return 	$this
     */
    public function withCancelWaitingDate($value)
    {
        $clone = clone $this;
        $clone->cancel_waiting_date = $value;
        return $clone;
    }

    /**
     * Get booker
     *
     * @return 	string
     */
    public function getBooker()
    {
        return $this->booker;
    }

    /**
     * Set booker with $value
     *
     * @param 	string 	$value
     * @return 	$this
     */
    public function withBooker(string $value)
    {
        $clone = clone $this;
        $clone->booker = $value;
        return $clone;
    }

    public function getAdditionalFields() : array
    {
        return $this->additional_fields;
    }

    public function withAdditionalFields(array $fields) : Overview
    {
        $clone = clone $this;
        $clone->additional_fields = $fields;
        return $clone;
    }
}

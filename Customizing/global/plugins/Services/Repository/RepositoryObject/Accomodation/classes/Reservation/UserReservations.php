<?php
namespace CaT\Plugins\Accomodation\Reservation;

use CaT\Plugins\Accomodation\Reservation\Note\Note;

/**
 * This is a collection of reservations for a distinct user at an oac-object.
 * Note, that reservations might be empty.
 *
 * @author 	Nils Haagen 	<nils.haagen@concepts-and-training.de>
 */
class UserReservations
{

    /**
     * @var int
     */
    protected $usr_id;

    /**
     * @var Reservation[]
     */
    protected $reservations;

    /**
     * @var Note|null
     */
    protected $note;

    /**
     * @param int 	$usr_id
     * @param Reservation[]		$reservations
     */
    public function __construct(int $usr_id, array $reservations, Note $note = null)
    {
        $this->usr_id = $usr_id;
        $this->reservations = $reservations;
        $this->note = $note;
    }


    /**
     * Get the User's id.
     *
     * @return int
     */
    public function getUserId()
    {
        return (int) $this->usr_id;
    }

    /**
     * Get the reservations for the user (at this oac-object).
     *
     * @return Reservation[]
     */
    public function getReservations()
    {
        return $this->reservations;
    }

    /**
     * Does the user have any reservations?
     *
     * @return bool
     */
    public function hasReservations()
    {
        return count($this->reservations) > 0;
    }

    /**
     * @return Note|null
     */
    public function getNote()
    {
        return $this->note;
    }
}

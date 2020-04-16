<?php

namespace CaT\Plugins\Accomodation\Reservation;

/**
 * Interface for DB handle of reservations
 */
interface DB
{
    /**
     * Get reservations for a single user at the accomodation object.
     *
     * @param int 	$usr_id
     * @param int 	$aco_obj_id
     * @return Reservation[]
     */
    public function selectForUserInObject(int $usr_id, int $aco_obj_id) : array;

    /**
     * Get an array of reservations by user
     *
     * @param int 	$oac_obj_id
     * @return array<int,Reservations[]>
     */
    public function selectAllForObj(int $oac_obj_id) : array;

    /**
     * Update a reservation
     *
     * @param \Reservation 	$reservation
     * @return void
     */
    public function update(Reservation $reservation);

    /**
     * Delete a Reservation
     *
     * @param int 	$id
     * @return void
     */
    public function deleteForId(int $id);

    /**
     * Delete all Reservations for user at object
     *
     * @param int 	$oac_obj_id
     * @param int 	$usr_id
     * @return void
     */
    public function deleteAllUserReservations(int $oac_obj_id, int $usr_id);

    /**
     * Delete all Reservations for object
     *
     * @param int 	$oac_obj_id
     * @return void
     */
    public function deleteAllForObj(int $oac_obj_id);

    /**
     * create a Reservation
     *
     * @param int 	$oac_obj_id
     * @param int 	$usr_id
     * @param string $date
     * @param bool 	$selfpay
     * @return Reservation
     */
    public function createReservation(
        int $oac_obj_id,
        int $usr_id,
        string $date,
        bool $selfpay
    ) : Reservation;


    /**
     * Delete all Reservations for usr (globally, for every object)
     *
     * @param int 	$usr_id
     * @return void
     */
    public function deleteAllForUser(int $usr_id);

    /**
     * Get a UserReservations-object.
     *
     * @param int 	$oac_obj_id
     * @param int 	$usr_id
     * @return array
     */
    public function getUserReservations(int $oac_obj_id, int $usr_id) : array;
}

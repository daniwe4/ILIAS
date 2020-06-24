<?php

namespace CaT\Plugins\Webinar\VC\Generic;

use CaT\Plugins\Webinar\VC;

/**
 * Interface to save settings and imported user for Generic VC
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface DB
{
    /**
     * Create a new Generic VC settings entry
     *
     * @param int 	$obj_id
     * @param string | null	$password
     * @param string | null	$tutor_login
     * @param string | null	$tutor_password
     * @param int | null 	$minutes_required
     *
     * @return Settings
     */
    public function create(
        int $obj_id,
        ?string $password,
        ?string $tutor_login,
        ?string $tutor_password,
        ?int $minutes_required
    ) : Settings;

    /**
     * Update an existing Generic VC settings entry
     *
     * @param Settings 	$settings
     *
     * @return void
     */
    public function update(Settings $settings) : void;

    /**
     * Get Generic VC Settings
     *
     * @param int 	$obj_id
     *
     * @return Settings
     */
    public function select(int $obj_id) : Settings;

    /**
     * Delete Generic VC settings entry
     *
     * @param int 	$obj_id
     *
     * @return void
     */
    public function delete(int $obj_id) : void;

    /**
     * Creates an user entry if user is not booked on vc
     *
     * @param int 	$obj_id
     * @param string 	$user_name
     * @param string 	$email
     * @param string 	$phone
     * @param string 	$company
     * @param int 	$minutes
     *
     * @return VC\Participant
     */
    public function createUnknownParticipant(
        int $obj_id,
        string $user_name,
        string $email,
        string $phone,
        string $company,
        int $minutes
    ) : VC\Participant;

    /**
     * Delete all unknown participants
     *
     * @param int 	$obj_id
     *
     * @return void
     */
    public function deleteUnknownParticipants(int $obj_id) : void;

    /**
     * Get all unknown participants for obj id
     *
     * @param int 	$obj_id
     *
     * @return Participant[]
     */
    public function getUnknownParticipants(int $obj_id) : array;
}

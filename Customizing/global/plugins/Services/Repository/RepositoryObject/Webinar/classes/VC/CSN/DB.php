<?php

namespace CaT\Plugins\Webinar\VC\CSN;

/**
 * Interface to save settings and imported user for CSN VC
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface DB
{
    /**
     * Create a new CSN VC settings entry
     *
     * @param int 	$obj_id
     * @param string 	$phone
     * @param string 	$pin
     * @param int 	$minutes_required
     * @param bool 	$upload_required
     *
     * @return Settings
     */
    public function create($obj_id, $phone, $pin, $minutes_required, $upload_required = false);

    /**
     * Update an existing CSN VC settings entry
     *
     * @param Settings 	$settings
     *
     * @return null
     */
    public function update(Settings $settings);

    /**
     * Get CSN VC Settings
     *
     * @param int 	$obj_id
     *
     * @return Settings
     */
    public function select($obj_id);

    /**
     * Delete CSN VC settings entry
     *
     * @param int 	$obj_id
     *
     * @return null
     */
    public function delete($obj_id);

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
     * @return Participant
     */
    public function createUnkownParticipant($obj_id, $user_name, $email, $phone, $company, $minutes);

    /**
     * Delete all unknown participants
     *
     * @param int 	$obj_id
     *
     * @return null
     */
    public function deleteUnkownParticipants($obj_id);

    /**
     * Get all unknown participants for obj id
     *
     * @param int 	$obj_id
     *
     * @return Participant[]
     */
    public function getUnkownParticipants($obj_id);
}

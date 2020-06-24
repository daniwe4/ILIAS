<?php
declare(strict_types=1);

namespace CaT\Plugins\Webinar\VC\CSN;

use CaT\Plugins\Webinar\VC;

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
     * @param string|null 	$phone
     * @param string|null 	$pin
     * @param int|null 	$minutes_required
     * @param bool 	$upload_required
     *
     * @return Settings
     */
    public function create(
        int $obj_id,
        ?string $phone,
        ?string $pin,
        ?int $minutes_required,
        bool $upload_required = false
    ) : Settings;

    /**
     * Update an existing CSN VC settings entry
     *
     * @param Settings 	$settings
     *
     * @return void
     */
    public function update(Settings $settings) : void;

    /**
     * Get CSN VC Settings
     *
     * @param int 	$obj_id
     *
     * @return Settings
     */
    public function select(int $obj_id) : Settings;

    /**
     * Delete CSN VC settings entry
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

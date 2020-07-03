<?php

namespace CaT\Plugins\OnlineSeminar\VC;

use CaT\Libs\ExcelWrapper\Spout;

/**
 * Interface for actions of any VC type
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface VCActions
{
    /**
     * Get participant by user name
     *
     * @param string 	$user_name
     *
     * @return Participant | null
     */
    public function getParticipantByUserName(string $user_name) : ?Participant;

    /**
     * Creates an imported user entry as Participant
     *
     * @param string 	$user_name
     * @param string 	$email
     * @param string 	$phone
     * @param string 	$company
     * @param int 	$minutes
     * @param int | null	$user_id
     *
     * @return Participant
     */
    public function createUnknownParticipant(
        string $user_name,
        string $email,
        string $phone,
        string $company,
        int $minutes,
        ?int $user_id = null
    ) : Participant;

    /**
     * Get a single unknown participant by user name
     *
     * @param string 	$user_name
     *
     * @return Participant | null
     */
    public function getUnknownParticipantByLogin(string $user_name) : ?Participant;

    /**
     * Get the minutes required
     *
     * @return int
     */
    public function getMinutesRequired() : int;

    /**
     * Update a booked participant with vc file data
     *
     * @param Participant 	$participant
     *
     * @return void
     */
    public function updateParticipant(Participant $participant) : void;
}

<?php

namespace CaT\Plugins\Webinar\VC;

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
    public function getParticipantByUserName($user_name);

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
    public function createUnkownParticipant($user_name, $email, $phone, $company, $minutes, $user_id = null);

    /**
     * Get a single unknown participant by user name
     *
     * @param string 	$user_name
     *
     * @return UnknownParticipant | null
     */
    public function getUnknownParticipantByLogin($user_name);

    /**
     * Get the minutes required
     *
     * @return int
     */
    public function getMinutesRequired();

    /**
     * Update a booked participant with vc file data
     *
     * @param Participant 	$participant
     *
     * @return null
     */
    public function updateParticipant(Participant $participant);
}

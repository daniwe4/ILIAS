<?php

namespace CaT\Plugins\EduTracking\Purposes\WBD;

interface WBDUserDataProvider
{

    /**
     * @param	int $usr_id
     * @return	array	contains in listed order:  $title, $firstname, $lastname, $phone, $mail
     */
    public function getUserInformation($user_id);
}

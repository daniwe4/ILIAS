<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace CaT\Plugins\TrainingProvider\Trainer;

/**
 * Interface for trainer database handle
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
interface DB
{

    /**
     * Install $this->plugin needed object like tables or sequences
     */
    public function install();

    /**
     * Create a new trainer. Object and DB
     *
     * @param string 	$title
     * @param string 	$salutation
     * @param string 	$firstname
     * @param string 	$lastname
     * @param null | int	$provider_id
     * @param string 	$email
     * @param string 	$phone
     * @param string 	$mobile_number
     * @param null | float	$fee
     * @param null | string	$extra_infos
     * @param bool 	$active
     *
     * @return \CaT\Plugins\TrainingProvider\Trainer\Trainer
     */
    public function create(
        $title,
        $salutation,
        $firstname,
        $lastname,
        $provider_id = null,
        $email = "",
        $phone = "",
        $mobile_number = "",
        $fee = null,
        $extra_infos = null,
        $active = true
    );

    /**
     * Get a trainer object
     *
     * @param int 	$id
     *
     * @return \CaT\Plugins\TrainingProvider\Trainer\Trainer
     */
    public function select($id);

    /**
     * Update a trainer
     *
     * @param \CaT\Plugins\TrainingProvider\Trainer\Trainer 	$trainer
     */
    public function update(\CaT\Plugins\TrainingProvider\Trainer\Trainer $trainer);

    /**
     * Delete a trainer
     *
     * @param int 	$id
     */
    public function delete($id);
}

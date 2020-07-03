<?php

namespace CaT\Plugins\OnlineSeminar\VC;

/**
 * Deliver and evaluates form settings form values required by vc
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface FormHelper
{
    /**
     * Adds vc required form items to settings form
     *
     * @param ilPropertyFormGUI 	$form
     *
     * @return void
     */
    public function addRequiredFormItems(\ilPropertyFormGUI $form);

    /**
     * Saves values from inout
     *
     * @param mixed[] 	$values
     *
     * @return void
     */
    public function saveRequiredValues(array $values);

    /**
     * Get vc values for filling form
     *
     * @param string[] 	$values
     *
     * @return void
     */
    public function getFormValues(array &$values);

    /**
     * Get properties for info screen
     *
     * @param ilInfoScreenGUI 	$info
     * @param bool 	$participant
     * @param bool 	$tutor
     *
     * @return void
     */
    public function addInfoProperties(\ilInfoScreenGUI $info, $participant = false, $tutor = false);
}

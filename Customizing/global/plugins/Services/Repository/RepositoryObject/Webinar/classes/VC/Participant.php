<?php

declare(strict_types=1);

namespace CaT\Plugins\Webinar\VC;

/**
 * Interface for settings of any VC type
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface Participant
{
    public function isKnownUser() : bool;
}

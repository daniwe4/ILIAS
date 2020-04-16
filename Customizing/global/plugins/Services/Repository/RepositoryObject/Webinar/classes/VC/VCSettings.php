<?php

namespace CaT\Plugins\Webinar\VC;

/**
 * Interface for settings of any VC type
 *
 * @author Nils Haagen 	<nils.haagen@concepts-and-training.de>
 */
interface VCSettings
{
    /**
     * Get obj id
     *
     * @return int
     */
    public function getObjId();

    /**
     * Apply all values from (source) $settings to a copy of this instance.
     *
     * @param VCSettings
     * @return VCSettings
     */
    public function withValuesOf(VCSettings $settings);
}

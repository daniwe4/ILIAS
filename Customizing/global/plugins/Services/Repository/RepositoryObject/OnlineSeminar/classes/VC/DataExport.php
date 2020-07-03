<?php

namespace CaT\Plugins\OnlineSeminar\VC;

/**
 * Interface for export of VC data
 *
 * @author Stefan Hecken 	<stefan.hecken@cocnepts-and-training.de>
 */
interface DataExport
{
    /**
     * Start an export of the VC Data
     *
     * @return void
     */
    public function run();
}

<?php

namespace CaT\Plugins\EduTracking\Purposes\WBD;

/**
 * Description how to ahndle wbd settings in data store system
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface DB
{
    /**
     * Create a empty config set
     *
     * @param	\ilObjEduTracking	$obj
     *
     * @return	WBD
     */
    public function create(\ilObjEduTracking $obj);

    /**
     * Selects current settings for obj
     *
     * @param	\ilObjEduTracking	$obj
     *
     * @throws \Exception if no settings are found
     *
     * @return	WBD
     */
    public function selectFor(\ilObjEduTracking $obj);

    /**
     * Updates existing settings
     *
     * @param 	WBD	$settings
     *
     * @return void
     */
    public function update(WBD $settings);

    /**
     * Deletes settings for obj
     *
     * @param	\ilObjEduTracking	$obj
     *
     * @return	void
     */
    public function deleteFor(\ilObjEduTracking $obj);
}

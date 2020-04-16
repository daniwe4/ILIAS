<?php

namespace CaT\Plugins\EduTracking\Purposes\GTI;

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
     * @return	GTI
     */
    public function create(\ilObjEduTracking $obj);

    /**
     * Selects current settings for obj
     *
     * @param	\ilObjEduTracking	$obj
     *
     * @throws	\Exception if no settings are found
     *
     * @return	GTI
     */
    public function selectFor(\ilObjEduTracking $obj);

    /**
     * Updates existing settings
     *
     * @param	GTI	$settings
     *
     * @return	void
     */
    public function update(GTI $settings);

    /**
     * Deletes settings for obj
     *
     * @param	\ilObjEduTracking	$obj
     *
     * @return	void
     */
    public function deleteFor(\ilObjEduTracking $obj);
}

<?php
namespace CaT\Plugins\CourseMember\LPSettings;

/**
 * Manages the lp of every user to given obj
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface LPManager
{
    /**
     * Refresh the lp state
     *
     * @param int 	$obj_id
     *
     * @return void
     */
    public function refresh($obj_id);
}

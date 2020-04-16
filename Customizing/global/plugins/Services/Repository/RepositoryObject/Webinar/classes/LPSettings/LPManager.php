<?php
namespace CaT\Plugins\Webinar\LPSettings;

interface LPManager
{
    /**
     * Refresh the lp state
     *
     * @param int 	$obj_id
     *
     * @return null
     */
    public function refresh($obj_id);

    /**
     * Check user has passed the course
     *
     * @param int 	$needed
     * @param string[] 	$lp_data
     *
     * @return bool
     */
    public function coursePassed($needed, array $lp_data);
}

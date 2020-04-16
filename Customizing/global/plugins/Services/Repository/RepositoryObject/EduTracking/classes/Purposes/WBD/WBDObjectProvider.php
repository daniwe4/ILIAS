<?php

namespace CaT\Plugins\EduTracking\Purposes\WBD;

interface WBDObjectProvider
{
    public function getFirstChildOfByType($ref_id, $search_type);
}

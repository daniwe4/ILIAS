<?php

namespace CaT\Plugins\MaterialList\RPC\Procedures\Course;

use CaT\Plugins\MaterialList\RPC;

/**
 * Procedure to get the minimum amount of member
 */
class ilGetMinMembers extends RPC\ilFunctionBase
{
    const COLUMN_TITLE = "xlsx_min_member_count";

    /**
     * Return the number of members
     *
     * @return int
     */
    public function run()
    {
        return new RPC\FunctionResult($this->txt(self::COLUMN_TITLE), (int) $this->crs->getSubscriptionMinMembers());
    }
}

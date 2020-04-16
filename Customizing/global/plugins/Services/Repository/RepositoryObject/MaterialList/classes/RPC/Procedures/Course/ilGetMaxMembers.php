<?php

namespace CaT\Plugins\MaterialList\RPC\Procedures\Course;

use CaT\Plugins\MaterialList\RPC;

/**
 * Procedure to get the maximum amount of member
 */
class ilGetMaxMembers extends RPC\ilFunctionBase
{
    const COLUMN_TITLE = "xlsx_max_member_count";

    /**
     * Return the title of crs
     *
     * @return int
     */
    public function run()
    {
        return new RPC\FunctionResult($this->txt(self::COLUMN_TITLE), (int) $this->crs->getSubscriptionMaxMembers());
    }
}

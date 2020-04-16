<?php

namespace CaT\Plugins\MaterialList\RPC\Procedures\Custom;

use CaT\Plugins\MaterialList\RPC;

/**
 * Procedure to get the amount of current booked member
 */
class ilGetMemberCount extends RPC\ilFunctionBase
{
    const COLUMN_TITLE = "xlsx_member_count";

    /**
     * Return the title of crs
     *
     * @return string
     */
    public function run()
    {
        return new RPC\FunctionResult($this->txt(self::COLUMN_TITLE), $this->crs->getMembersObject()->getCountMembers());
    }
}

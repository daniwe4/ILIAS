<?php

namespace CaT\Plugins\MaterialList\RPC\Procedures\Course;

use \CaT\Plugins\MaterialList\RPC;

/**
 * Procedure to get the title
 */
class ilGetTitle extends RPC\ilFunctionBase
{
    const COLUMN_TITLE = "xlsx_course_title";

    /**
     * Return the title of crs
     *
     * @return string
     */
    public function run()
    {
        return new RPC\FunctionResult($this->txt(self::COLUMN_TITLE), $this->crs->getTitle());
    }
}

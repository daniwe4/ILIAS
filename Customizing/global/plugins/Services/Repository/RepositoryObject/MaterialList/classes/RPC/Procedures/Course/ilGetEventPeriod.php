<?php

namespace CaT\Plugins\MaterialList\RPC\Procedures\Course;

use CaT\Plugins\MaterialList\RPC;

/**
 * Procedure to get the event period
 */
class ilGetEventPeriod extends RPC\ilFunctionBase
{
    const COLUMN_TITLE = "xlsx_event_period";
    /**
     * Return the title of crs
     *
     * @return string
     */
    public function run()
    {
        $crs_start = "";
        $crs_end = "";

        if ($this->crs->getCourseStart()) {
            $crs_start = $this->crs->getCourseStart()->get(IL_CAL_FKT_DATE, "d.m.Y");
        }

        if ($this->crs->getCourseEnd()) {
            $crs_end = $this->crs->getCourseEnd()->get(IL_CAL_FKT_DATE, "d.m.Y");
        }

        return new RPC\FunctionResult($this->txt(self::COLUMN_TITLE), $crs_start . " - " . $crs_end);
    }
}

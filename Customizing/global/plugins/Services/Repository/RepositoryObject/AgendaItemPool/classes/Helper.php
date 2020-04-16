<?php

namespace CaT\Plugins\AgendaItemPool;

trait Helper
{
    /**
     * Check the array as argument is null or an array only with int
     *
     * @param int[]|null 	$ints
     *
     * @return bool
     */
    protected function checkIntArray(array $ints = null)
    {
        if (!$ints) {
            return true;
        }

        foreach ($ints as $value) {
            if (!is_int($value)) {
                return false;
            }
        }

        return true;
    }
}

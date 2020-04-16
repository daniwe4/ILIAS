<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues;

/**
 * Trait for helping functions
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
trait Helper
{
    /**
     * Checks the db value is null
     *
     * @param string|int|float|null 	$value
     * @param string|int|float 			$default
     *
     * @return string|int|float
     */
    protected function getDefaultOnNull($value, $default)
    {
        if ($value === null) {
            return $default;
        }

        return $value;
    }
}

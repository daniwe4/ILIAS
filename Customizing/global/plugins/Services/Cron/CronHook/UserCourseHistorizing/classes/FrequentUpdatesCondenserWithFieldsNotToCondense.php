<?php

namespace CaT\Plugins\UserCourseHistorizing;

use CaT\Historization\Condenser\FrequentUpdates\FrequentUpdatesCondenser;

/**
 * @inheritDoc
 *
 * This Condenser is able to skip fields at condensing. These fields are configured in a protected
 * static property. If there will be more than one, it might be an option to add these fields
 * as param to the constructor.
 */
class FrequentUpdatesCondenserWithFieldsNotToCondense extends FrequentUpdatesCondenser
{
    /**
     * This pool of fields might not be condensed.
     * So we are able to to get the new value, no matter if it is null or something else
     * if there is a value in the previous entry
     * @var string[]
     */
    protected static $fields_not_to_condense = [
        "wbd_booking_id"
    ];

    /**
     * @inheritDoc
     */
    protected function completeFirstBySecond($first, $second)
    {
        foreach ($this->hist_case->payloadFields() as $field) {
            $skip_replace = false;
            if (in_array($field, static::$fields_not_to_condense)) {
                if (
                    array_key_exists($field, $first) &&
                    !is_null($first[$field]) &&
                    trim($first[$field]) === ""
                ) {
                    $first[$field] = null;
                    $skip_replace = true;
                }
            }

            if (
                !$skip_replace &&
                (
                    !array_key_exists($field, $first) ||
                    $first[$field] === null
                )
            ) {
                if (array_key_exists($field, $second)) {
                    $first[$field] = $second[$field];
                } else {
                    $first[$field] = null;
                }
            }
        }
        return $first;
    }
}

<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\Agenda\EntryChecks;

class EntryChecks
{
    const MIN_AMOUNT_ENTRIES = 1;
    const MIN_DURATION_TIME = 5;
    const MAX_DURATION_TIME = 24 * 60;

    /**
     * Checks agenda entries for time spans
     *
     * @param string[] 	$pool_items
     * @param string[] 	$start_times
     * @param string[] 	$end_times
     *
     * @return CheckObject[]
     */
    public function getCheckObjects(
        array $pool_items,
        array $durations,
        array $deleted_id
    ) {
        $to_check = [];
        foreach ($durations as $key => $value) {
            if (!array_key_exists($key, $deleted_id)) {
                $to_check[] = $this->createCheckObject(
                    (int) $value,
                    $pool_items[$key]
                );
            }
        }


        return $to_check;
    }

    public function createCheckObject(
        int $duration,
        $pool_item_id
    ) : CheckObject {
        return new CheckObject(
            $duration,
            $pool_item_id
        );
    }

    public function checkPoolItemSelected(array $to_check) : bool
    {
        foreach ($to_check as $key => $object) {
            if ($object->getPoolItemId() == "") {
                return false;
            }
        }

        return true;
    }

    public function checkMinimumAmount(array $to_check) : bool
    {
        return count($to_check) >= self::MIN_AMOUNT_ENTRIES;
    }

    public function checkMinimumAgendaDuration(array $to_check) : bool
    {
        if (!is_array($to_check) || count($to_check) == 0) {
            return false;
        }
        foreach ($to_check as $key => $entry) {
            if ($entry->getDuration() < self::MIN_DURATION_TIME) {
                return false;
            }
        }

        return true;
    }

    public function checkMaxDuration(array $to_check, int $start = 0) : bool
    {
        if (!is_array($to_check) || count($to_check) == 0) {
            return true;
        }

        $sum = $start;
        foreach ($to_check as $key => $entry) {
            $sum += $entry->getDuration();
        }
        return $sum < self::MAX_DURATION_TIME;
    }
}

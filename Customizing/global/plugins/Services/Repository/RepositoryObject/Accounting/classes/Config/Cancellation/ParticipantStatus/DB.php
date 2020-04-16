<?php

declare(strict_types=1);

namespace CaT\Plugins\Accounting\Config\Cancellation\ParticipantStatus;

interface DB
{
    /**
     * @param int[] $states
     */
    public function saveStates(array $states);

    /**
     * @return int[]
     */
    public function getStates() : array;
}

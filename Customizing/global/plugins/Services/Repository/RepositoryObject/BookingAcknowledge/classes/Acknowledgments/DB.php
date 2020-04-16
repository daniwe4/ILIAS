<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\BookingAcknowledge\Acknowledgments;

/**
 * Storage for Acknowledgments
 */
interface DB
{
    public function create(
        int $acting_usr_id,
        int $usr_id,
        int $crs_ref_id,
        int $state
    ) : Acknowledgment;
}

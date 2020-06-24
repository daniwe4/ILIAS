<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace CaT\Plugins\CourseCreation;

use ILIAS\TMS\CourseCreation\Request;

/**
 * A database for requests.
 */
interface RequestDB
{
    /**
     * @param   int     $user_id
     * @param   string  $session_id
     * @param   int     $crs_ref_id
     * @param   int     $new_parent_ref_id
     * @param   array<int,int> $copy_options
     * @param   array<int,mixed> $configuration
     * @param   \DateTime $requested_ts
     * @return  Request
     */
    public function create(
        int $user_id,
        string $session_id,
        int $crs_ref_id,
        int $new_parent_ref_id,
        array $copy_options,
        array $configuration,
        \DateTime $requested_ts
    );

    /**
     * @param   Request $request
     * @return  void
     */
    public function update(Request $request);

    /**
     * @return	Request|null
     */
    public function getNextDueRequest();

    /**
     * @param	int	$user_id
     * @return	Request[]
     */
    public function getDueRequestsOf(int $user_id);
}

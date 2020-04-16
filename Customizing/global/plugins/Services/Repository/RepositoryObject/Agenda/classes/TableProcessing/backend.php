<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\Agenda\TableProcessing;

interface backend
{
    /**
     * Delete the option in record
     */
    public function delete(array $record);

    /**
     * Checks option in record if it is valid
     * If not fills key errors with values
     */
    public function valid(array $record) : array;

    /**
     * Update an existing option
     */
    public function update(array $record) : array;

    /**
     * Creates a new option
     */
    public function create(array $record) : array;
}

<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\ScaledFeedback\Feedback;

interface DB
{
    /**
     * Install the table into the db
     */
    public function install();

    /**
     * Create a new entry for any feedback and returns the new object
     */
    public function create(
        int $obj_id,
        int $set_id,
        int $usr_id,
        int $dim_id
        ) : Feedback;

    public function update(Feedback $feedback);

    /**
     * @return 	Feedback[]
     */
    public function selectAll() : array;

    /**
     * Select an entry by obj id and set id.
     */
    public function selectByIds(int $obj_id, int $set_id) : array;

    /**
     * Get amount of feedbacks for set
     */
    public function getAmountOfFeedbacks(int $obj_id, int $set_id) : int;

    public function delete(int $id);
}

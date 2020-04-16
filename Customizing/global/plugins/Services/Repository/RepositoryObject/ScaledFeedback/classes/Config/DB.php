<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\ScaledFeedback\Config;

use CaT\Plugins\ScaledFeedback\Config\Sets\Set;
use CaT\Plugins\ScaledFeedback\Config\Dimensions\Dimension;

interface DB
{
    /**
     * Install the tables into the db
     */
    public function install();

    public function createSet(
        string $title,
        bool $is_locked,
        int $min_submissions
    ) : Set;

    public function updateSet(Set $set);

    /**
     * @return Set[]
     */
    public function selectAllSets() : array;

    public function selectSetById(int $id) : Set;

    /**
     * @param int[] $ids
     */
    public function deleteSets(array $ids);

    public function createDimension(string $title, string $displayed_title) : Dimension;

    public function updateDimension(Dimension $dimension);

    /**
     * @return 	Dimension[]
     */
    public function selectAllDimensions(string $filter = "") : array;

    public function selectDimensionById(int $id) : Dimension;

    public function deleteDimensions(array $ids);
}

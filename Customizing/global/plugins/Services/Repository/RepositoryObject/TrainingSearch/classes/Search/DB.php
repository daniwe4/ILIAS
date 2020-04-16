<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\TrainingSearch\Search;

interface DB
{
    /**
     * @return Course[]
     */
    public function getCoursesFor(Options $options) : array;
}

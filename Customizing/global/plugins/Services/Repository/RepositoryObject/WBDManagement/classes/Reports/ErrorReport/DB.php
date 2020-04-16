<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WBDManagement\Reports\ErrorReport;

interface DB
{
    /**
     * @param int[]
     * @return Entry[]
     */
    public function getErrorInfosFor(array $ids) : array;
    public function setStatusToResolved(int $id);
    public function setStatusToNotResolvable(int $id);
}

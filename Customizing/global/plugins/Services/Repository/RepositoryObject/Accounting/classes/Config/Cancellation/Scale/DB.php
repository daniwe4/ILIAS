<?php

declare(strict_types=1);

namespace CaT\Plugins\Accounting\Config\Cancellation\Scale;

use \LogicException;

interface DB
{
    public function addScale(int $span_start, int $span_end, int $percent) : Scale;
    public function update(Scale $scale);
    /**
     * @return Scale[]
     */
    public function getScales() : array;

    /**
     * @throws LogicException if no scale is found
     */
    public function getScaleFor(int $days) : Scale;
    public function delete(int $id);
}

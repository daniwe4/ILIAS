<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\BookingApprovals\Player;

use CaT\Plugins\BookingApprovals\Approvals\BookingRequest;

/**
 * build StepsProcessors
 */
class StepsProcessorFactory
{
    /**
     * @var \ILIAS\DI\Container
     */
    protected $dic;

    public function __construct(\ILIAS\DI\Container $dic)
    {
        $this->dic = $dic;
    }

    public function processor(BookingRequest $booking_request) : StepsProcessor
    {
        return new StepsProcessor(
            $this->dic,
            $booking_request
        );
    }
}

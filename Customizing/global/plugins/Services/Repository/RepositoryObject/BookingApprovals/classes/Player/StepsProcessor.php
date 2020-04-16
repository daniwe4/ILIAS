<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\BookingApprovals\Player;

use CaT\Plugins\BookingApprovals\Approvals\BookingRequest;
use CaT\Ente\ILIAS\ilHandlerObjectHelper;
use ILIAS\TMS\Booking\StepAdapter;

/**
 * Process steps that were stored during request.
 */
class StepsProcessor
{
    use ilHandlerObjectHelper;

    const STEP_COMPONENT_CLASS_SELF = 'ILIAS\TMS\Booking\SelfBookingWithApprovalsStep';
    const STEP_COMPONENT_CLASS_SUPERIOR = 'ILIAS\TMS\Booking\SuperiorBookingWithApprovalsStep';

    const ERROR_ALL_OK = 1;
    const ERROR_DIFFERENT_AMOUNT_OF_STEPS = 2;
    const ERROR_PROCESSING_STEPS = 3;
    const ERROR_USER_ALREADY_BOOKED = 4;

    public function __construct(
        \ILIAS\DI\Container $dic,
        BookingRequest $booking_request
    ) {
        $this->dic = $dic;
        $this->booking_request = $booking_request;
        $this->entity_ref_id = $booking_request->getCourseRefId();
    }

    /**
     * Try to process booking-steps with stored data.
     */
    public function process() : int
    {
        $step_data = $this->unpackStepData();
        $crs_ref_id = $this->booking_request->getCourseRefId();
        $usr_id = $this->booking_request->getUserId();
        $steps = $this->getApplicableSteps();

        //this is a safety-check for double requests:
        //currently, cancel steps are also booking steps
        //thus, when a a user is already on the course and he steps are requested,
        //we get cancel steps instead of actual booking steps.
        foreach ($steps as $step) {
            if ($step instanceof \CaT\Plugins\BookingModalities\Steps\CancelStep
                || $step instanceof \CaT\Plugins\BookingModalities\Steps\HardCancelStep
            ) {
                return self::ERROR_USER_ALREADY_BOOKED;
            }
        }

        //process steps with data
        if (count($steps) !== count($step_data)) {
            return self::ERROR_DIFFERENT_AMOUNT_OF_STEPS;
        }

        for ($i = 0; $i < count($steps); $i++) {
            $step = $steps[$i]
                ->withActingUser($this->booking_request->getActingUserId());
            $data = $step_data[$i];
            try {
                $step->processStep($crs_ref_id, $usr_id, $data);
            } catch (\Exception $e) {
                return self::ERROR_PROCESSING_STEPS;
            }
        }
        return self::ERROR_ALL_OK;
    }

    /**
     * @inheritdoc
     */
    protected function getDIC() : \ILIAS\DI\Container
    {
        return $this->dic;
    }

    /**
     * @inheritdoc
     */
    protected function getEntityRefId() : int
    {
        return $this->entity_ref_id;
    }

    protected function getStepComponentClass() : string
    {
        if ($this->booking_request->getUserId() === $this->booking_request->getActingUserId()) {
            return self::STEP_COMPONENT_CLASS_SELF;
        } else {
            return self::STEP_COMPONENT_CLASS_SUPERIOR;
        }
    }

    protected function unpackStepData() : array
    {
        $booking_data = json_decode($this->booking_request->getBookingData(), true);
        $step_data = [];
        foreach (array_values($booking_data) as $data) {
            $step_data[] = $data;
        }
        return $step_data;
    }

    protected function getApplicableSteps() : array
    {
        $available_steps = $this->getComponentsOfType($this->getStepComponentClass());
        $applicable_steps = array_values(
            array_filter(
                $available_steps,
                function ($step) {
                    return $step->isApplicableFor($this->booking_request->getUserId());
                }
            )
        );

        usort($applicable_steps, function ($a, $b) {
            if ($a->getPriority() < $b->getPriority()) {
                return -1;
            }
            if ($a->getPriority() > $b->getPriority()) {
                return 1;
            }
            return 0;
        });

        return $applicable_steps;
    }
}

<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\BookingApprovals\Player;

use ILIAS\TMS\Booking;
use ILIAS\TMS\Wizard;
use CaT\Plugins\BookingApprovals\Approvals;
use CaT\Plugins\BookingApprovals\Utils;

/**
 * Player for booking requests.
 */
class RequestPlayer extends Wizard\Player
{
    const TXT_PLAYER_FINISHED = 'booking_request_created';

    /**
     * @var	Approvals\Actions
     */
    protected $actions;

    /**
     * @var	Utils\CourseUtils
     */
    protected $course_utils;

    /**
     * @var	\ilAppEventHandler
     */
    protected $event_handler;

    public function __construct(
        Wizard\ILIASBindings $ilias_bindings,
        Wizard\Wizard $wizard,
        Wizard\StateDB $state_db,
        Approvals\Actions $approval_actions,
        Utils\CourseUtils $course_utils
    ) {
        $this->actions = $approval_actions;
        $this->course_utils = $course_utils;
        parent::__construct($ilias_bindings, $wizard, $state_db);
    }

    /**
     * Finish the wizard by storing the steps' data and
     * creating BookingRequests/Approvals
     *
     * @param	State	$state
     * @return	void
     */
    protected function finish(Wizard\State $state)
    {
        $steps = $this->wizard->getSteps();
        assert('$state->getStepNumber() == count($steps)');
        if ($state->getStepNumber() !== count($steps)) {
            throw new \LogicException("User did not work through the wizard.");
        }

        $data = [];
        for ($i = 0; $i < count($steps); $i++) {
            $step = $steps[$i];
            $data['step_' . (string) $i] = $state->getStepData($i);
        }

        $payload = json_encode($data);
        $crs_ref_id = $this->wizard->getEntityRefId();
        $crs_id = \ilObject::_lookupObjId($crs_ref_id);
        $target_user_id = $this->wizard->getUserId();
        $acting_user_id = $this->wizard->getActingUserId();

        if ($target_user_id == $acting_user_id) {
            $approvals = $this->course_utils->getApprovalRolesForSelfBooking($crs_ref_id);
        } else {
            $approvals = $this->course_utils->getApprovalRolesForSuperiorBooking($crs_ref_id);
        }

        $this->actions->requestBooking(
            $acting_user_id,
            $target_user_id,
            $crs_ref_id,
            $crs_id,
            $approvals,
            $payload
        );

        $this->state_db->delete($state);
        //$this->wizard->finish();	//events are fired by the ApprovalActions
        $msg = sprintf(
            $this->ilias_bindings->txt(self::TXT_PLAYER_FINISHED),
            $this->course_utils->getTitleForRefId($crs_ref_id),
            $this->course_utils->lookupFullname($target_user_id)
        );
        $messages = array($msg);
        $this->ilias_bindings->redirectToPreviousLocation($messages, true);
    }
}

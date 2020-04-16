<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\TrainerOperations;

/**
 * Provides "speaking" methods for permission-checks.
 */
class AccessHelper
{
    /**
     * @var ilAccess
     */
    protected $access;

    /**
     * @var \ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var int
     */
    protected $obj_ref_id;

    /**
     * @var int
     */
    protected $current_user_id;


    public function __construct(
        \ilAccess $access,
        \ilCtrl $g_ctrl,
        int $obj_ref_id,
        int $current_user_id
    ) {
        $this->access = $access;
        $this->obj_ref_id = $obj_ref_id;
        $this->g_ctrl = $g_ctrl;
        $this->current_user_id = $current_user_id;
    }

    protected function checkStdAccess(string $permission) : bool
    {
        return $this->access->checkAccess($permission, "", $this->obj_ref_id);
    }

    protected function checkOrgAccess(string $permission) : bool
    {
        return $this->access->checkPositionAccess($permission, $this->obj_ref_id);
    }

    public function mayEditSettings() : bool
    {
        return $this->checkStdAccess('write');
    }

    public function maySeeCockpitItem() : bool
    {
        return $this->checkStdAccess('visible')
            && $this->checkStdAccess('read');
    }

    public function mayEditOwnCalendars() : bool
    {
        return $this->checkStdAccess(ObjTrainerOperations::OP_EDIT_OWN_CALENDARS);
    }

    public function mayEditGeneralCalendars() : bool
    {
        return $this->checkStdAccess(ObjTrainerOperations::OP_EDIT_GENERAL_CALENDARS);
    }

    public function maySeeUnassingedDates() : bool
    {
        return $this->checkStdAccess(ObjTrainerOperations::OP_SEE_UNASSIGNED);
    }

    public function maySeeForeignCalendars() : bool
    {
        return $this->checkOrgAccess(ObjTrainerOperations::ORGU_OP_SEE_OTHER_CALENDARS);
    }

    public function maySeeGeneralCalendars() : bool
    {
        return $this->checkStdAccess(ObjTrainerOperations::OP_SEE_GENERAL);
    }


    public function getCurrentUserId() : int
    {
        return $this->current_user_id;
    }

    public function redirectInfo($msg)
    {
        \ilUtil::sendFailure($msg, true);
        $link = $this->g_ctrl->getLinkTargetByClass(
            "ilinfoscreengui",
            "showSummary",
            "",
            false,
            false
        );
        \ilUtil::redirect($link);
    }

    public function mayEditMembersAtCourse(int $crs_ref_id)
    {
        return $this->access->checkAccess('manage_members', "", $crs_ref_id);
    }
}

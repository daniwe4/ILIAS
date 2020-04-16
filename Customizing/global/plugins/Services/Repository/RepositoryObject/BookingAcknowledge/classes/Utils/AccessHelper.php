<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\BookingAcknowledge\Utils;

use CaT\Plugins\BookingAcknowledge\BookingAcknowledge;

/**
 *
 */
class AccessHelper
{
    /**
     * @var ilAccess
     */
    protected $access;

    /**
     * @var int
     */
    protected $obj_ref_id;

    public function __construct(
        \ilAccess $access,
        int $obj_ref_id
    ) {
        $this->access = $access;
        $this->obj_ref_id = $obj_ref_id;
    }

    public function checkAccess(string $permission) : bool
    {
        return
            $this->access->checkAccess($permission, "", $this->obj_ref_id)
            ||
            $this->access->checkPositionAccess($permission, $this->obj_ref_id)
            ;
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

    public function mayAcknowledge() : bool
    {
        return
            $this->checkStdAccess(BookingAcknowledge::OP_ACKNOWLEDGE)
            || $this->checkOrgAccess(BookingAcknowledge::ORGU_OP_ACKNOWLEDGE);
    }

    public function mayViewReport() : bool
    {
        return
            $this->checkStdAccess('read')
            || $this->checkOrgAccess(BookingAcknowledge::ORGU_OP_SEE_USERBOOKINGS);
    }

    public function maySeeCockpitItem() : bool
    {
        return
            $this->checkStdAccess('visible')
            || $this->checkOrgAccess(BookingAcknowledge::ORGU_OP_SEE_USERBOOKINGS);
    }
}

<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\BookingApprovals\Utils;

/**
 * A facade for commonly used ilias-dependencies.
 */
class IliasWrapper
{
    public function getInstanceByRefId(int $ref_id) : \ilObject
    {
        return \ilObjectFactory::getInstanceByRefId($ref_id);
    }

    public function lookupObjId(int $ref_id) : int
    {
        return \ilObject::_lookupObjId($ref_id);
    }

    public function lookupFullname(int $usr_id) : string
    {
        return \ilObjUser::_lookupFullname($usr_id);
    }

    public function isPluginActive(string $plugin_id) : bool
    {
        return \ilPluginAdmin::isPluginActive($plugin_id);
    }

    public function lookupTitle(int $obj_id) : string
    {
        return \ilObject::_lookupTitle($obj_id);
    }

    public function lookupTitleByRef(int $obj_ref_id) : string
    {
        $obj_id = $this->lookupObjId($obj_ref_id);
        return $this->lookupTitle($obj_id);
    }
}

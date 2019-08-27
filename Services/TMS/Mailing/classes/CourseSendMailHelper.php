<?php

declare(strict_types=1);

namespace ILIAS\TMS\Mailing;

trait CourseSendMailHelper
{
    abstract public function getDIC();
    abstract public function getEntityId();
    abstract public function getEntityRefId();

    public function isCourseMailingActive()
    {
        if (!\ilPluginAdmin::isPluginActive("xcml")) {
            return false;
        }

        return true;
    }

    public function isCourseMailingInSubTree()
    {
        return in_array("xcml", $this->getSubTreeTypes());
    }

    public function isCopySettingsInSubTree()
    {
        return in_array("xcps", $this->getSubTreeTypes());
    }

    public function isCourseOnline()
    {
        $dummy = null;
        return \ilObjCourseAccess::_isActivated($this->getEntityId(), $dummy, false);
    }

    protected function getSubTreeTypes()
    {
        $tree = $this->getDIC()["tree"];

        return $tree->getSubTreeTypes($this->getEntityRefId());
    }
}

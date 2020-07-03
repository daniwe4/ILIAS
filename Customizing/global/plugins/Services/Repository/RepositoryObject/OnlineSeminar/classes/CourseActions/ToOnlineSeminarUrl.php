<?php

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace CaT\Plugins\OnlineSeminar\CourseActions;

use ILIAS\TMS;
use \ILIAS\TMS\MyUsersHelper;

/**
 * This is an information about a course action, noteworthy for a user in some context.
 * Return the configured url of the online seminar.
 */
class ToOnlineSeminarUrl extends TMS\CourseActionImpl
{
    use MyUsersHelper;

    const DEFAULT_PROTOCOL = 'http://';
    const PROTOCOLS = ['http://', 'https://'];

    /**
     * @inheritdoc
     */
    public function isAllowedFor($usr_id)
    {
        return $this->hasAccess($this->owner->getRefId());
    }

    /**
     * @inheritdoc
     */
    public function getLink(\ilCtrl $ctrl, $usr_id)
    {
        return $this->getConfiguredLink();
    }

    protected function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    public function getConfiguredLink()
    {
        $lnk = $this->owner->getSettings()->getUrl();
        if (trim($lnk) === '') {
            return '';
        }

        foreach (self::PROTOCOLS as $protocol) {
            if ($this->startsWith($lnk, $protocol)) {
                return $lnk;
            }
        }
        return self::DEFAULT_PROTOCOL . $lnk;
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        $txt = $this->owner->txtClosure();
        return $txt("to_online_seminar_url");
    }

    /**
     * Has user read access to the course
     *
     * @param int 	$crs_ref_id
     *
     * @return bool
     */
    protected function hasAccess($crs_ref_id)
    {
        $access = $this->getAccess();
        //check for permission only, circumventing online/offline-check.
        return $access->doRBACCheck(
            'read',
            '',
            $this->owner->getRefId(),
            $this->getCurrentUserId(),
            'xwbr'
        );
    }

    protected function getAccess()
    {
        if (is_null($this->access)) {
            global $DIC;
            $this->access = $DIC["ilAccess"];
        }

        return $this->access;
    }

    protected function getCurrentUserId()
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        return $ilUser->getId();
    }
}

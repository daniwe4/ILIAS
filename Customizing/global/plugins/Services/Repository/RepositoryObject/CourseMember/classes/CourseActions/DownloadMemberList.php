<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */
namespace CaT\Plugins\CourseMember\CourseActions;

use ILIAS\TMS;

/**
 * This presents an action to download the signature list
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class DownloadMemberList extends TMS\CourseActionImpl
{
    /**
     * @inheritdoc
     */
    public function isAllowedFor($usr_id)
    {
        $file_storage = $this->owner->getFileStorage();
        return $this->hasAccess($this->owner->getRefId()) && !$file_storage->isEmpty();
    }

    /**
     * @inheritdoc
     */
    public function getLink(\ilCtrl $ctrl, $usr_id)
    {
        return $this->owner->getLinkForMemberlistDownload();
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        global $DIC;
        $txt = $this->owner->txtClosure();
        return $txt("download_file");
    }

    /**
     * Check user has access to course member object
     *
     * @param int 	$cm_ref_id
     *
     * @return bool
     */
    protected function hasAccess($cm_ref_id)
    {
        global $DIC;
        $access = $DIC->access();
        if ($access->checkAccess("read", "", $cm_ref_id)
            && $access->checkAccess("view_lp", "", $cm_ref_id)
        ) {
            return true;
        }

        return false;
    }
}

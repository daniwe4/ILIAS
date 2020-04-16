<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */
namespace CaT\Plugins\CourseMember\CourseActions;

use ILIAS\TMS;

/**
 * This presents an action to download the member list
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class DownloadSignatureList extends TMS\CourseActionImpl
{
    /**
     * @inheritdoc
     */
    public function isAllowedFor($usr_id)
    {
        $parent = $this->getParentCourse();
        $default_template_id = $this->getDefaultTemplateId();
        return !is_null($parent) &&
            !is_null($default_template_id) &&
            $this->hasAccess($this->owner->getRefId()) &&
            \ilPluginAdmin::isPluginActive('docdeliver')
        ;
    }

    /**
     * @inheritdoc
     */
    public function getLink(\ilCtrl $ctrl, $usr_id)
    {
        if (!\ilPluginAdmin::isPluginActive('docdeliver')) {
            return "";
        }

        $parent = $this->getParentCourse();
        $default_template_id = $this->getDefaultTemplateId();
        if (is_null($parent) && is_null($default_template_id)) {
            return "";
        }

        $crs_id = (int) $parent->getId();
        $template_id = $this->getCourseSelectedTemplate($crs_id);
        if (is_null($template_id)) {
            $template_id = $default_template_id;
        }

        /** @var ilDocumentDeliveryPlugin $xcmb */
        $docdeliver = \ilPluginAdmin::getPluginObjectById('docdeliver');
        return $docdeliver->getLinkForSignatureList($crs_id, $template_id);
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        $txt = $this->owner->txtClosure();
        return $txt("download_blank_file");
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

    protected function getParentCourse()
    {
        return $this->owner->getParentCourse();
    }

    protected function getDefaultTemplateId()
    {
        return $this->getPluginObject()->getMemberListDefaultTemplateId();
    }

    protected function getCourseSelectedTemplate(int $crs_id)
    {
        return $this->getPluginObject()->getSelectedCourseTemplate($crs_id);
    }

    protected function getPluginObject() : \ilCourseMemberPlugin
    {
        return $this->owner->getPluginObject();
    }
}

<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

use ILIAS\TMS;

/**
 * This actions allows user to recommend a course
 */
class SendRecommendationMail extends TMS\CourseActionImpl
{
    const SEND_RECOMMENDATION_ID = "SR01";

    /**
     * @inheritdoc
     */
    public function isAllowedFor($usr_id)
    {
        $course = $this->entity->object();
        $data = $this->getContentBuilderDataForTemplateId(
            self::SEND_RECOMMENDATION_ID,
            $this->entity->object()->getRefId()
        );
        return $this->hasAccess($course->getRefId()) &&
            !$this->userIsAnonymous($usr_id) &&
            !is_null($data->getTemplateId())
        ;
    }

    /**
     * @inheritdoc
     */
    public function getLink(\ilCtrl $ctrl, $usr_id)
    {
        $data = $this->getContentBuilderDataForTemplateId(
            self::SEND_RECOMMENDATION_ID,
            $this->entity->object()->getRefId()
        );

        if (is_null($data->getTemplateId())) {
            return "";
        }

        $subject = rawurlencode($data->getSubject());
        $body = rawurlencode($data->getPlainMessage());

        return "mailto:?body=$body&subject=$subject";
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        global $DIC;
        $lng = $DIC->language();
        return $lng->txt("send_recommendation");
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
        global $DIC;
        $access = $DIC->access();
        if (
            $access->checkAccess("visible", "", $crs_ref_id)
        ) {
            return true;
        }

        return false;
    }

    protected function getContentBuilderDataForTemplateId(
        string $id,
        int $crs_ref_id
    ) : ilTMSMailContentBuilder {
        $mail = new ilTMSMailing();
        $contexts = $mail->getStandardContexts();
        $contexts["ilTMSMailContextCourse"] = new ilTMSMailContextCourse($crs_ref_id);
        $contexts["ilTMSMailContextUser"] = new ilTMSMailContextUser($this->current_user_id);
        $cb = $mail->getContentBuilder();
        $cb = $cb->withContexts($contexts);
        return $cb->withData($id);
    }

    public function openInNewTab() : bool
    {
        return false;
    }

    protected function userIsAnonymous($usr_id)
    {
        return $usr_id == ANONYMOUS_USER_ID;
    }
}

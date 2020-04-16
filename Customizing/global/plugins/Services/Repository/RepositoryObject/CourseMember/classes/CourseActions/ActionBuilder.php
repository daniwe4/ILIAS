<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace CaT\Plugins\CourseMember\CourseActions;

use CaT\Ente\Entity;
use ILIAS\TMS\ActionBuilderBase;

/**
 * This is an information about a course action, noteworthy for a user in some context.
 */
class ActionBuilder extends ActionBuilderBase
{
    protected function getMyTrainingActions(bool $with_recommendation_action) : array
    {
        $ret = [
            800 => new ToCourseMember($this->entity, $this->owner, $this->user, 800),
            900 => new DownloadMemberList($this->entity, $this->owner, $this->user, 900)
        ];

        if (!$this->isFinalized() || !$this->isSignatureListUploaded()) {
            $ret[700] = new DownloadSignatureList($this->entity, $this->owner, $this->user, 700);
        }

        return $ret;
    }

    protected function getMyAdministratedTrainingActions(bool $with_recommendation_action) : array
    {
        $ret = [
            800 => new ToCourseMember($this->entity, $this->owner, $this->user, 800),
            900 => new DownloadMemberList($this->entity, $this->owner, $this->user, 900)
        ];

        if (!$this->isFinalized() || !$this->isSignatureListUploaded()) {
            $ret[700] = new DownloadSignatureList($this->entity, $this->owner, $this->user, 700);
        }

        return $ret;
    }

    protected function getTepSessionDetailActions(bool $with_recommendation_action) : array
    {
        $ret = [
            800 => new ToCourseMember($this->entity, $this->owner, $this->user, 800),
            900 => new DownloadMemberList($this->entity, $this->owner, $this->user, 900)
        ];

        if (!$this->isFinalized() || !$this->isSignatureListUploaded()) {
            $ret[700] = new DownloadSignatureList($this->entity, $this->owner, $this->user, 700);
        }

        return $ret;
    }

    protected function isFinalized()
    {
        if (is_null($this->is_finalized)) {
            $this->is_finalized = $this->owner->getSettings()->getClosed();
        }
        return $this->is_finalized;
    }

    protected function isSignatureListUploaded()
    {
        if (is_null($this->is_file_uploaded)) {
            $this->is_file_uploaded = !$this->owner->getFileStorage()->isEmpty();
        }
        return $this->is_file_uploaded;
    }
}

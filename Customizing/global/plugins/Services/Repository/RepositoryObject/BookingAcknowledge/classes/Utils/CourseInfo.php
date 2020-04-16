<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\BookingAcknowledge\Utils;

use ILIAS\TMS\CourseInfo as TMSCourseInfo;
use ILIAS\TMS\CourseInfoHelper;
use CaT\Ente\ILIAS\ilHandlerObjectHelper;

class CourseInfo
{
    use ilHandlerObjectHelper;
    use CourseInfoHelper;

    /**
     * @var ilObjectDataCache
     */
    protected $obj_data_cache;

    /**
     * @var int
     */
    protected $ref_id;

    /**
     * @var ilObjCourse
     */
    protected $crs_obj;

    /**
     * @var array
     */
    protected $crs_info_cache;


    public function __construct(
        \ArrayAccess $dic,
        \ilObjectDataCache $obj_data_cache
    ) {
        $this->dic = $dic;
        $this->obj_data_cache = $obj_data_cache;
        $this->crs_info_cache = [];
    }

    public function withRefId(int $ref) : CourseInfo
    {
        $clone = clone $this;
        $clone->ref_id = $ref;
        return $clone;
    }

    public function getRefId() : int
    {
        $this->checkRefId();
        return $this->ref_id;
    }

    public function getId() : int
    {
        return (int) $this->obj_data_cache->lookupObjId($this->getRefId());
    }

    public function getTitle() : string
    {
        return $this->obj_data_cache->lookupTitle($this->getId());
    }

    /**
     * @return int[]
     */
    public function getAdminIds() : array
    {
        $participants = $this->getCourseObject()->getMembersObject();
        return array_map('intval', $participants->getAdmins());
    }

    public function getCourseDates() : string
    {
        return $this->getCourseInfoValue('date');
    }

    public function getCourseType() : string
    {
        return $this->getCourseInfoValue('type');
    }

    protected function getCourseObject()
    {
        if (!$this->crs_obj) {
            $this->crs_obj = \ilObjectFactory::getInstanceByRefId($this->getRefId());
        }
        return $this->crs_obj;
    }

    protected function checkRefId()
    {
        if ($this->ref_id === -1) {
            throw new LogicException("There was no ref-id configured at CourseInfo", 1);
        }
    }

    /**
     * @inheritdoc
     */
    protected function getDIC()
    {
        return $this->dic;
    }

    /**
     * @inheritdoc
     */
    protected function getEntityRefId()
    {
        return $this->getRefId();
    }

    protected function getCourseInfoFromEnte() : array
    {
        if (!$this->crs_info_cache) {
            $this->crs_info_cache = $this->getCourseInfo(
                TMSCourseInfo::CONTEXT_APPROVALS_OVERVIEW,
                true,
                true
            );
        }
        return $this->crs_info_cache;
    }

    protected function getCourseInfoValue(string $field) : string
    {
        $crs_info = $this->getCourseInfoFromEnte();
        foreach ($crs_info as $info) {
            if ($info->getLabel() === $field) {
                return $info->getValue();
            }
        }
        return '';
    }
}

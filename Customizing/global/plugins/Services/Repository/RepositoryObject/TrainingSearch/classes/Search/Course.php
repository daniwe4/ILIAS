<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\TrainingSearch\Search;

use CaT\Ente\ILIAS\ilHandlerObjectHelper;
use ILIAS\TMS\ActionBuilder;
use ILIAS\TMS\CourseInfo;
use ILIAS\TMS\CourseInfoHelper;
use ILIAS\TMS\CourseAction;
use ILIAS\TMS\ActionBuilderUserHelper;

class Course
{
    /**
     * @var \ilObjCourse
     */
    protected $course;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string[]
     */
    protected $target_group;

    /**
     * @var string
     */
    protected $goals;

    /**
     * @var string[]
     */
    protected $topics;

    /**
     * @var string
     */
    protected $location;

    /**
     * @var string
     */
    protected $address;

    /**
     * @var string
     */
    protected $fee;

    /**
     * @var ActionBuilder[] |null
     */
    protected $action_builder = null;

    /**
     * @var CourseAction[] | null
     */
    protected $actions = null;

    public function __construct(
        \ilObjCourse $course,
        string $type,
        array $target_group,
        string $goals,
        array $topics,
        string $location,
        string $address,
        string $fee
    ) {
        $this->course = $course;
        $this->type = $type;
        $this->target_group = $target_group;
        $this->goals = $goals;
        $this->topics = $topics;
        $this->location = $location;
        $this->address = $address;
        $this->fee = $fee;
    }

    public function getObjId() : int
    {
        return (int) $this->course->getId();
    }

    public function getRefId() : int
    {
        return (int) $this->course->getRefId();
    }

    public function getTitle() : string
    {
        return $this->course->getTitle();
    }

    public function getType() : string
    {
        return $this->type;
    }

    /**
     * @return \ilDateTime | null
     */
    public function getBeginDate()
    {
        return $this->course->getCourseStart();
    }

    /**
     * @return \ilDateTime | null
     */
    public function getEndDate()
    {
        return $this->course->getCourseEnd();
    }

    /**
     * @return string[]
     */
    public function getTargetGroup() : array
    {
        return $this->target_group;
    }

    public function getGoals() : string
    {
        return $this->goals;
    }

    /**
     * @return string[]
     */
    public function getTopics() : array
    {
        return $this->topics;
    }

    public function getLocation() : string
    {
        return $this->location;
    }

    public function getAddress() : string
    {
        return $this->address;
    }

    public function geFee() : string
    {
        return $this->fee;
    }

    // TODO: this propably doesn't belong here. This might be removed or consolidated
    // once the search logic is turned into a proper db-query. This also deserves tests.

    use ilHandlerObjectHelper;
    use CourseInfoHelper;
    use ActionBuilderUserHelper;

    /**
     * @var	CourseInfo[]|null
     */
    protected $short_info = null;

    /**
     * @var	CourseInfo[]|null
     */
    protected $detail_info = null;

    protected function getDIC()
    {
        return $GLOBALS["DIC"];
    }

    protected function getEntityRefId()
    {
        return $this->getRefId();
    }

    protected function getEntity()
    {
        return new \CaT\Ente\ILIAS\Entity(
            $this->course
        );
    }

    protected function getShortInfo()
    {
        if ($this->short_info === null) {
            $this->short_info = $this->getCourseInfo(CourseInfo::CONTEXT_SEARCH_SHORT_INFO);
        }
        return $this->short_info;
    }

    protected function getDetailInfo()
    {
        if ($this->detail_info === null) {
            $this->detail_info = $this->getCourseInfo(CourseInfo::CONTEXT_SEARCH_DETAIL_INFO);
        }
        return $this->detail_info;
    }

    protected function getFurtherInfo()
    {
        if ($this->small_detail_info === null) {
            $this->small_detail_info = $this->getCourseInfo(CourseInfo::CONTEXT_SEARCH_FURTHER_INFO);
        }
        return $this->small_detail_info;
    }

    protected function getIsBookable()
    {
        if ($this->is_bookable === null) {
            $this->is_bookable = $this->getCourseInfo(CourseInfo::CONTEXT_IS_BOOKABLE);
        }
        return $this->is_bookable;
    }

    protected function getIDDRelevant()
    {
        if ($this->idd_relevant === null) {
            $this->idd_relevant = $this->getCourseInfo(CourseInfo::CONTEXT_IDD_RELEVANT);
        }
        return $this->idd_relevant;
    }

    public function getTitleValue()
    {
        // Take most important info as title
        $short_info = $this->getShortInfo();
        if (count($short_info) > 0) {
            return $short_info[0]->getValue();
        }
        return $this->getUnknownString();
    }

    public function getSubTitleValue()
    {
        // Take second most important info as subtitle
        $short_info = $this->getShortInfo();
        if (count($short_info) > 1) {
            return $short_info[1]->getValue();
        }
        return $this->getUnknownString();
    }

    public function getImportantFields()
    {
        // Take info 2-7 as fields in header line
        $short_info = $this->getShortInfo();
        return $this->unpackValue(array_slice($short_info, 2, 5));
    }

    public function getFurtherFields()
    {
        // Take info 2 to end as fields in header line
        return $this->unpackLabelAndNestedValue($this->getUIFactory(), $this->getFurtherInfo());
    }

    public function getDetailFields()
    {
        $detail_info = $this->getDetailInfo();
        if (count($detail_info) > 0) {
            return $this->unpackLabelAndNestedValue($this->getUIFactory(), $this->getDetailInfo());
        }

        return ["" => $this->getNoDetailInfoMessage()];
    }

    /**
     * Returns the course is bookable or not
     *
     * @return bool
     */
    public function isBookable()
    {
        $is_bookable = $this->getIsBookable();
        return count($is_bookable) > 0;
    }

    /**
     * Returns the course is idd relevant or not
     *
     * @return bool
     */
    public function isIDDRelevant()
    {
        $idd_relevant = $this->getIDDRelevant();
        return count($idd_relevant) > 0;
    }

    public function getActions(int $context, int $usr_id, bool $is_recommendation_allowed)
    {
        if (is_null($this->actions)) {
            $this->actions = $this->getActionsFor(
                $context,
                $usr_id,
                $is_recommendation_allowed
            );
        }
        return $this->actions;
    }

    protected function getActionsFor(int $context, int $usr_id, bool $is_recommendation_allowed) : array
    {
        $action_builders = $this->getActionBuilder();
        $actions = [];
        foreach ($action_builders as $action_builder) {
            $actions[] = $action_builder->getCourseActionsFor(
                $context,
                $usr_id,
                $is_recommendation_allowed
            );
        }
        $actions = $this->mergeActions($actions);
        ksort($actions);
        return $actions;
    }

    /**
     * @return \ILIAS\TMS\ActionBuilder[]|null
     */
    public function getActionBuilder()
    {
        if ($this->action_builder === null) {
            $this->action_builder = $this->getActionBuilders();
        }
        return $this->action_builder;
    }

    /**
     * Get the UI-factory.
     *
     * @return ILIAS\UI\Factory
     */
    public function getUIFactory()
    {
        global $DIC;
        return $DIC->ui()->factory();
    }

    /**
     * Get a string that is "unknown" in the users language.
     *
     * @return string
     */
    protected function getUnknownString()
    {
        global $DIC;
        $lng = $DIC["lng"];
        return $lng->txt("unknown");
    }

    /**
     * Get a string that is "unknown" in the users language.
     *
     * @return string
     */
    protected function getNoDetailInfoMessage()
    {
        global $DIC;
        $lng = $DIC["lng"];
        return $lng->txt("no_detail_infos");
    }
}

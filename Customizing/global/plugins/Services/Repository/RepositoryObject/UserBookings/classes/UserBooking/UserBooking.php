<?php

namespace CaT\Plugins\UserBookings\UserBooking;

use CaT\Ente\ILIAS\ilHandlerObjectHelper;
use ILIAS\TMS\ActionBuilder;
use ILIAS\TMS\CourseInfo;
use ILIAS\TMS\CourseInfoHelper;
use ILIAS\TMS\CourseAction;
use ILIAS\TMS\ActionBuilderUserHelper;

/**
 * This is the object for additional settings.
 */
class UserBooking
{
    /**
     * @var	int
     */
    protected $ref_id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var ilDateTime
     */
    protected $begin_date;

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
     * @var ilDateTime
     */
    protected $end_date;

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
     * @var CourseAction[] | null
     */
    protected $actions = null;

    /**
     * @var ActionBuilder[] | null
     */
    protected $action_builders = null;

    public function __construct(
        int $ref_id,
        string $title,
        string $type,
        \ilDateTime $begin_date = null,
        array $target_group,
        string $goals,
        array $topics,
        \ilDateTime $end_date = null,
        string $location,
        string $address,
        string $fee
    ) {
        $this->ref_id = $ref_id;
        $this->title = $title;
        $this->type = $type;
        $this->begin_date = $begin_date;
        $this->target_group = $target_group;
        $this->goals = $goals;
        $this->topics = $topics;
        $this->end_date = $end_date;
        $this->location = $location;
        $this->address = $address;
        $this->fee = $fee;
    }

    public function getRefId()
    {
        return $this->ref_id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getBeginDate()
    {
        return $this->begin_date;
    }

    public function getTargetGroup()
    {
        return $this->target_group;
    }

    public function getGoals()
    {
        return $this->goals;
    }

    public function getTopics()
    {
        return $this->topics;
    }

    public function getEndDate()
    {
        return $this->end_date;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getFee()
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
        return $this->ref_id;
    }

    protected function getShortInfo()
    {
        if ($this->short_info === null) {
            $this->short_info = $this->getCourseInfo(CourseInfo::CONTEXT_USER_BOOKING_SHORT_INFO);
        }
        return $this->short_info;
    }

    protected function getDetailInfo()
    {
        if ($this->detail_info === null) {
            $this->detail_info = $this->getCourseInfo(CourseInfo::CONTEXT_USER_BOOKING_DETAIL_INFO);
        }
        return $this->detail_info;
    }

    protected function getFurtherInfo()
    {
        if ($this->small_detail_info === null) {
            $this->small_detail_info = $this->getCourseInfo(CourseInfo::CONTEXT_USER_BOOKING_FURTHER_INFO);
        }
        return $this->small_detail_info;
    }

    public function getActions(int $context, int $usr_id, $with_recommendation_action) : array
    {
        if (is_null($this->actions)) {
            $this->actions = $this->getActionsFor($context, $usr_id, $with_recommendation_action);
        }
        return $this->actions;
    }

    protected function getActionBuilder()
    {
        if ($this->action_builders === null) {
            $this->action_builders = $this->getActionBuilders();
        }
        return $this->action_builders;
    }

    protected function getActionsFor(
        int $context,
        int $usr_id,
        bool $with_recommendation_action
    ) : array {
        $action_builders = $this->getActionBuilder();
        $actions = [];
        foreach ($action_builders as $action_builder) {
            $actions[] = $action_builder->getCourseActionsFor(
                $context,
                $usr_id,
                $with_recommendation_action
            );
        }
        $actions = $this->mergeActions($actions);
        ksort($actions);
        return $actions;
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
     * Form date for gui as user timezone string
     *
     * @param ilDateTime 	$dat
     * @param bool 	$use_time
     *
     * @return string
     */
    protected function formatDate($dat, $use_time = false)
    {
        global $DIC;
        $user = $DIC["ilUser"];
        require_once("Services/Calendar/classes/class.ilCalendarUtil.php");
        $out_format = \ilCalendarUtil::getUserDateFormat($use_time, true);
        $ret = $dat->get(IL_CAL_FKT_DATE, $out_format, $user->getTimeZone());
        if (substr($ret, -5) === ':0000') {
            $ret = substr($ret, 0, -5);
        }

        return $ret;
    }

    public function getSearchActionLinks($usr_id)
    {
        $search_actions = $this->getSearchActions($usr_id);
        $ret = array();
        foreach ($search_actions as $search_action) {
            if ($search_action->isAllowedFor($usr_id)) {
                $ret[] = $search_action;
            }
        }

        return $ret;
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

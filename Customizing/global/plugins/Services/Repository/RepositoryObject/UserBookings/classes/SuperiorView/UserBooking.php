<?php

declare(strict_types=1);

namespace CaT\Plugins\UserBookings\SuperiorView;

use CaT\Ente\ILIAS\ilHandlerObjectHelper;
use ILIAS\TMS\ActionBuilder;
use ILIAS\TMS\ActionBuilderUserHelper;
use ILIAS\TMS\CourseInfo;
use ILIAS\TMS\CourseInfoHelper;
use ILIAS\TMS\CourseAction;

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
     * @var	int
     */
    protected $user_id;

    /**
     * @var string
     */
    protected $fullname;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var \ilDate
     */
    protected $begin_date;

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
        int $user_id,
        string $fullname,
        string $title,
        \ilDate $begin_date
    ) {
        $this->ref_id = $ref_id;
        $this->user_id = $user_id;
        $this->fullname = $fullname;
        $this->title = $title;
        $this->begin_date = $begin_date;
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

    protected function getEntityRefId() : int
    {
        return $this->ref_id;
    }

    public function getUserId() : int
    {
        return $this->user_id;
    }

    public function getFullname() : string
    {
        return $this->fullname;
    }

    public function getRefId() : int
    {
        return $this->ref_id;
    }

    public function getBeginDate() : \ilDate
    {
        return $this->begin_date;
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
            $this->small_detail_info = $this->getCourseInfo(CourseInfo::CONTEXT_USER_BOOKING_SUPERIOR_FURTHER_INFO);
        }
        return $this->small_detail_info;
    }

    protected function getActionBuilder() : array
    {
        if (is_null($this->action_builders)) {
            $this->action_builders = $this->getActionBuilders();
        }
        return $this->action_builders;
    }

    public function getActions(int $context, int $usr_id, $with_recommendation_action) : array
    {
        if (is_null($this->actions)) {
            $this->actions = $this->getActionsFor($context, $usr_id, $with_recommendation_action);
        }
        return $this->actions;
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

    public function getTitleValue() : string
    {
        return $this->title;
    }

    public function getImportantFields()
    {
        // Take info 2-7 as fields in header line
        $short_info = $this->getShortInfo();
        return $this->unpackValue(array_slice($short_info, 1, 5));
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

<?php

namespace CaT\Plugins\TrainingAdminOverview\AdministratedTrainings;

use CaT\Ente\ILIAS\ilHandlerObjectHelper;
use ILIAS\TMS\ActionBuilder;
use ILIAS\TMS\CourseInfo;
use ILIAS\TMS\CourseInfoHelper;
use ILIAS\TMS\CourseAction;
use ILIAS\TMS\ActionBuilderUserHelper;

/**
 * Keeps basic informations about a course where user is booked as tutor.
 * It is also possible to get more informations about course via CaT\Ente
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class AdministratedTraining
{
    use ilHandlerObjectHelper;
    use CourseInfoHelper;
    use ActionBuilderUserHelper;

    /**
     * @var int
     */
    protected $crs_ref_id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var ilDateTime
     */
    protected $crs_start;

    /**
     * @var CourseInfo[] | null
     */
    protected $important_infos;

    /**
     * @var CourseInfo[] | null
     */
    protected $content_infos;

    /**
     * @var CourseInfo[] | null
     */
    protected $further_infos;

    /**
     * @var CourseInfo[] | null
     */
    protected $to_course_button_info;

    /**
     * @var CourseInfo[] | null
     */
    protected $course_member_button_info;

    /**
     * @var CourseAction[] | null
     */
    protected $actions = null;

    /**
     * @var ActionBuilder[] | null
     */
    protected $action_builders = null;

    /**
     * @param int 	$crs_ref_id
     * @param string 	$title
     */
    public function __construct(int $crs_ref_id, string $title, \ilDateTime $crs_start = null)
    {
        $this->crs_ref_id = $crs_ref_id;
        $this->title = $title;
        $this->crs_start = $crs_start;

        $this->important_infos = null;
        $this->content_infos = null;
        $this->further_infos = null;
        $this->to_course_button_info = null;
        $this->course_member_button_info = null;
    }

    /**
     * Get the dictionary object
     *
     * @return Object
     */
    protected function getDIC()
    {
        return $GLOBALS["DIC"];
    }

    /**
     * Get the language object
     *
     * @return Object
     */
    protected function getLng()
    {
        return $this->getDIC()->language();
    }

    /**
     * Returns the ref id of course
     *
     * @return int
     */
    protected function getEntityRefId()
    {
        return $this->crs_ref_id;
    }

    /**
     * Returns the ref id of course
     *
     * @return int
     */
    public function getRefId()
    {
        return $this->crs_ref_id;
    }

    /**
     * Get title of course
     *
     * @return string
     */
    public function getCourseTitle()
    {
        return $this->title;
    }

    /**
     * Get start date of course
     *
     * @return \ilDateTime | null
     */
    public function getCourseStart()
    {
        return $this->crs_start;
    }

    /**
     * Get important information via CaT\Ente
     *
     * @return CourseInfo[]
     */
    protected function getImportantInfos()
    {
        if ($this->important_infos === null) {
            $this->important_infos = $this->getCourseInfo(CourseInfo::CONTEXT_USER_BOOKING_SHORT_INFO);
        }
        return $this->important_infos;
    }

    /**
     * Get content information via CaT\Ente
     *
     * @return CourseInfo[]
     */
    protected function getContentInfos()
    {
        if ($this->content_infos === null) {
            $this->content_infos = $this->getCourseInfo(CourseInfo::CONTEXT_ADMIN_OVERVIEW_DETAIL_INFO);
        }
        return $this->content_infos;
    }

    /**
     * Get further information via CaT\Ente
     *
     * @return CourseInfo[]
     */
    protected function getFurtherInfos()
    {
        if ($this->further_infos === null) {
            $this->further_infos = $this->getCourseInfo(CourseInfo::CONTEXT_ADMIN_OVERVIEW_FURTHER_INFO);
        }
        return $this->further_infos;
    }

    public function getActions(int $context, int $usr_id) : array
    {
        if (is_null($this->actions)) {
            $this->actions = $this->getActionsFor(
                $context,
                $usr_id
            );
        }
        return $this->actions;
    }

    protected function getActionsFor(int $context, int $usr_id) : array
    {
        $action_builders = $this->getActionBuilder();
        $actions = [];
        foreach ($action_builders as $action_builder) {
            $actions[] = $action_builder->getCourseActionsFor($context, $usr_id);
        }

        $actions = $this->mergeActions($actions);
        ksort($actions);

        return $actions;
    }

    /**
     * Returns title of the course or a default message
     *
     * @return string
     */
    public function getTitle()
    {
        $important_infos = $this->getImportantInfos();
        if (count($important_infos) > 0) {
            return $important_infos[0]->getValue();
        }
        return $this->getUnknownString();
    }

    /**
     * Return all important informations
     *
     * @return string[]
     */
    public function getImportantValue()
    {
        $important_infos = $this->getImportantInfos();
        return $this->unpackValue(array_slice($important_infos, 1, 5));
    }

    /**
     * Return all content informations
     *
     * @return string[]
     */
    public function getContent()
    {
        $content_infos = $this->getContentInfos();
        if (count($content_infos) > 0) {
            return $this->unpackLabelAndNestedValue($this->getUIFactory(), $content_infos);
        }

        return ["" => $this->getNoDetailInfoMessage()];
    }

    /**
     * Returns all further informations
     *
     * @return string[]
     */
    public function getFurtherFields()
    {
        return $this->unpackLabelAndNestedValue($this->getUIFactory(), $this->getFurtherInfos());
    }

    /**
     * Get a string that is "unknown" in the users language.
     *
     * @return string
     */
    protected function getUnknownString()
    {
        return $this->getLng()->txt("unknown");
    }

    /**
     * Get a string that is "unknown" in the users language.
     *
     * @return string
     */
    protected function getNoDetailInfoMessage()
    {
        return $this->getLng()->txt("no_detail_infos");
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
}

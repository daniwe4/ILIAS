<?php

namespace CaT\Plugins\EduTracking;

use \CaT\Ente\ILIAS\SeparatedUnboundProvider;
use \CaT\Ente\ILIAS\Entity;
use \ILIAS\TMS\CourseInfo;
use \ILIAS\TMS\CourseInfoImpl;
use \ILIAS\TMS\Mailing;

class UnboundProvider extends SeparatedUnboundProvider
{
    /**
     * @inheritdocs
     */
    public function componentTypes()
    {
        return [CourseInfo::class, Mailing\MailContext::class];
    }

    /**
     * Build the component(s) of the given type for the given object.
     *
     * @param   string    $component_type
     * @param   Entity    $provider
     * @return  Component[]
     */
    public function buildComponentsOf($component_type, Entity $entity)
    {
        $owner = $this->owner();
        $actions = $owner->getActionsFor("IDD");
        $this->txt = $owner->txtClosure();

        if ($component_type === CourseInfo::class) {
            $ret = array();

            $ret = $this->getCourseInfoForLearningTime($ret, $entity, $actions);

            return $ret;
        }

        if ($component_type === Mailing\MailContext::class) {
            return [new MailContextEduTracking($entity, $this->owner())];
        }
        return array();
    }

    /**
     * Get course info for booking deadline
     *
     * @return CourseInfo[]
     */
    protected function getCourseInfoForLearningTime(array $ret, Entity $entity, Purposes\IDD\ilActions $actions)
    {
        $settings = $actions->select();
        $minutes = $settings->getMinutes();

        if ($minutes !== null) {
            $hours = floor($minutes / 60);
            $concrete_minutes = $minutes - $hours * 60;

            $value = str_pad($hours, 2, 0, STR_PAD_LEFT) . ":" . str_pad($concrete_minutes, 2, 0, STR_PAD_LEFT);
            $ret[] = $this->buildDetailInfo(
                $entity,
                "",
                $this->txt("learning_time") . " " . $value . " " . $this->txt("hours"),
                525,
                [
                 CourseInfo::CONTEXT_SEARCH_SHORT_INFO,
                 CourseInfo::CONTEXT_USER_BOOKING_SHORT_INFO
                ]
            );

            $ret[] = $this->buildDetailInfo(
                $entity,
                $this->txt("learning_time") . ": ",
                $value . " " . $this->txt("hours"),
                100,
                [
                    CourseInfo::CONTEXT_SEARCH_FURTHER_INFO,
                    CourseInfo::CONTEXT_USER_BOOKING_FURTHER_INFO,
                    CourseInfo::CONTEXT_USER_BOOKING_SUPERIOR_FURTHER_INFO,
                    CourseInfo::CONTEXT_ASSIGNED_TRAINING_FURTHER_INFO,
                    CourseInfo::CONTEXT_ADMIN_OVERVIEW_FURTHER_INFO
                ]
            );

            $ret[] = $this->buildDetailInfo(
                $entity,
                $this->txt("learning_time"),
                $value . " " . $this->txt("hours"),
                920,
                [CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO]
            );

            if ($minutes > 0) {
                $ret[] = $this->buildDetailInfo(
                    $entity,
                    "",
                    1,
                    10,
                    [CourseInfo::CONTEXT_IDD_RELEVANT]
                );
            }

            $ret[] = $this->buildDetailInfo(
                $entity,
                "idd",
                $value . " " . $this->txt("hours"),
                1000,
                [CourseInfo::CONTEXT_APPROVALS_OVERVIEW]
            );
        }

        return $ret;
    }

    /**
     * Createa a courseInfoImpl object for detailed infos
     *
     * @param Entity 	$entity
     * @param string 	$lable
     * @param string 	$value
     * @param int 	$step
     * @param string 	$context
     *
     * @return CourseInfoImpl
     */
    protected function buildDetailInfo(Entity $entity, $label, $value, $step, array $context)
    {
        return new CourseInfoImpl(
            $entity,
            $label,
            $value,
            "",
            $step,
            $context
        );
    }

    /**
     * Parse lang code to text
     *
     * @param string 	$code
     *
     * @return string
     */
    protected function txt($code)
    {
        $txt = $this->txt;
        return $txt($code);
    }
}

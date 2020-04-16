<?php

namespace CaT\Plugins\Accounting;

use \CaT\Ente\ILIAS\SeparatedUnboundProvider;
use \CaT\Ente\ILIAS\Entity;
use \ILIAS\TMS\CourseInfo;
use \ILIAS\TMS\CourseInfoImpl;
use \ILIAS\TMS\Mailing as TMSMailing;
use \ILIAS\TMS\CourseCreation as CC;

class UnboundProvider extends SeparatedUnboundProvider
{
    /**
     * @inheritdocs
     */
    public function componentTypes()
    {
        return [
            CourseInfo::class,
            TMSMailing\MailContext::class,
            CC\Step::class
        ];
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
        // TODO: introduce an object, that can give the real value, not the key.
        // the object needs to bundle $cc and $actions.
        $actions = $this->owner()->getObjectActions();
        $this->txt = $this->owner()->txtClosure();
        if ($component_type === CourseInfo::class) {
            $ret = array();

            $ret = $this->getCourseInfoForFee($ret, $entity, $actions);

            return $ret;
        }

        if ($component_type === TMSMailing\MailContext::class) {
            return $this->getMailContext($entity);

            return $ret;
        }

        if ($component_type === CC\Step::class) {
            return $this->getCourseCreationSteps($entity);

            return $ret;
        }

        throw new \InvalidArgumentException("Unexpected component type '$component_type'");
    }

    /**
     * Get course info for fee
     *
     * @param CourseInfo[] 	$ret
     * @param Entity 	$entity
     * @param ilObjectActions 	$actions
     *
     * @return CourseInfo[]
     */
    protected function getCourseInfoForFee(array $ret, Entity $entity, ilObjectActions $actions)
    {
        $fee = $actions->getFeeActions()->select()->getFee();
        if (is_null($fee)) {
            $fee = "-";
        } else {
            $fee = number_format($fee, 2, ",", ".");
        }
        $ret[] = new CourseInfoImpl(
            $entity,
            $this->txt("fee_value") . ":",
            $fee,
            "",
            500,
            [CourseInfo::CONTEXT_SEARCH_FURTHER_INFO]
        );

        $ret[] = new CourseInfoImpl(
            $entity,
            $this->txt("fee_value") . ":",
            $fee,
            "",
            400,
            [
                CourseInfo::CONTEXT_USER_BOOKING_FURTHER_INFO,
                CourseInfo::CONTEXT_USER_BOOKING_SUPERIOR_FURTHER_INFO,
                CourseInfo::CONTEXT_ASSIGNED_TRAINING_FURTHER_INFO,
                CourseInfo::CONTEXT_ADMIN_OVERVIEW_FURTHER_INFO
            ]
        );

        $ret[] = new CourseInfoImpl(
            $entity,
            $this->txt("fee_value") . ":",
            $fee,
            "",
            1250,
            [CourseInfo::CONTEXT_BOOKING_DEFAULT_INFO]
        );

        $ret[] = new CourseInfoImpl(
            $entity,
            "fee",
            $fee,
            "",
            900,
            [CourseInfo::CONTEXT_APPROVALS_OVERVIEW]
        );

        return $ret;
    }

    /**
     * Createa a courseInfoImpl object for detailed infos
     *
     * @param Entity 	$entity
     * @param string 	$lable
     * @param string 	$value
     * @param int 	$step
     * @param string 	$contexts
     *
     * @return CourseInfoImpl
     */
    protected function buildDetailInfo($entity, $label, $value, $step, array $contexts)
    {
        return new CourseInfoImpl(
            $entity,
            $label,
            $value,
            "",
            $step,
            $contexts
        );
    }

    /**
     * Get the mail context for accounting
     *
     * @param Entity 	$entty
     *
     * @return TMSMailing\MailContext
     */
    protected function getMailContext(Entity $entity)
    {
        return [new Mailing\MailContextAccounting($entity, $this->owner())];
    }

    /**
     * Get all available steps for course creaition
     *
     * @param Entity 	$entity
     *
     * @return CC\Step[]
     */
    protected function getCourseCreationSteps(Entity $entity)
    {
        return [new CourseCreation\FeeStep($entity, $this->txt, $this->owner())];
    }

    /**
     * Parse lang code to lang value
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

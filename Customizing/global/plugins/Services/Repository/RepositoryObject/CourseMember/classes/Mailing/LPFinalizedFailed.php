<?php
namespace CaT\Plugins\CourseMember\Mailing;

use ILIAS\TMS\Booking;

/**
 * When a memberlist is finalized, members should recieve a mail with their status.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class LPFinalizedFailed extends LPStatusOccasion
{
    const TEMPLATE_IDENT = 'F02';
    const RELEVANT_LPSTATUS = \ilLPStatus::LP_STATUS_FAILED_NUM;


    /**
     * @inheritdoc
     */
    public function doesProvideMailForEvent($event)
    {
        assert(is_string($event));
        return in_array($event, self::$events);
    }
}

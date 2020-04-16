<?php
namespace CaT\Plugins\CourseMember\Mailing;

use ILIAS\TMS\Booking;

/**
 * When a memberlist is finalized, members should recieve a mail with their status.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class LPFinalizedAttended extends LPStatusOccasion
{
    const TEMPLATE_IDENT = 'F01';
    const RELEVANT_LPSTATUS = \ilLPStatus::LP_STATUS_COMPLETED_NUM;


    /**
     * @inheritdoc
     */
    public function doesProvideMailForEvent($event)
    {
        assert('is_string($event)');
        return in_array($event, self::$events);
    }
}

<?php
namespace CaT\Plugins\BookingModalities\MailingOccasions;

use ILIAS\TMS\Booking;
use ILIAS\TMS\Mailing;
use ILIAS\TMS\CourseInfoHelper;
use ILIAS\TMS\CourseInfo;

class SuperiorBookingToCourse extends MailOccasionBase
{
    use CourseInfoHelper;

    const TEMPLATE_IDENT = 'B03';

    protected static $events = array(
        Booking\Actions::EVENT_SUPERIOR_BOOKED_COURSE
    );

    /**
     * Get the attachments for the mail.
     *
     * @return void
     */
    protected function getAttachments(int $usr_id)
    {
        $iCal = $this->getICal();
        $attachment = new \ilTMSMailAttachment();
        $attachments = new \ilTMSMailAttachments();

        $crs_ref_id = $this->getCourseContext()->getCourseRefId();
        $crs = \ilObjectFactory::getInstanceByRefId($crs_ref_id);
        if ($crs->getCourseStart() == null) {
            return $attachments;
        }
        $comp = $this->getICalContexts();
        $ical_path = $iCal->saveICal(
            "crs_$crs_ref_id",
            $comp,
            sprintf($this->txt("calendar_entry"), $usr_id, $crs_ref_id)
        );
        $attachment = $attachment
            ->withAttachmentPath($ical_path);
        $attachments->addAttachment($attachment);
        return $attachments;
    }

    /**
     * @return Mailing\MailContext[]
     */
    protected function getICalContexts()
    {
        $components = $this->getCourseInfo(CourseInfo::CONTEXT_ICAL);
        return $components;
    }
}

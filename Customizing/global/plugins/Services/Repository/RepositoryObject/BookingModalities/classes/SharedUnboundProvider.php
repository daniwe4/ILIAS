<?php

namespace CaT\Plugins\BookingModalities;

use \CaT\Ente\ILIAS\SharedUnboundProvider as Base;
use \CaT\Ente\ILIAS\Entity;
use \ILIAS\TMS\Mailing\MailingOccasion;

class SharedUnboundProvider extends Base
{
    /**
     * @inheritdocs
     */
    public function componentTypes()
    {
        return [MailingOccasion::class];
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
        $owners = $this->owners();
        foreach ($owners as $owner) {
            if ($this->checkPermissionsOn((int) $owner->getRefId())) {
                $this->txt = $owner->txtClosure();
                if ($component_type === MailingOccasion::class) {
                    $booking_modus = $owner->getBooking()->getModus();
                    // Temporary fix for TMS-568
                    if ($booking_modus !== null && $booking_modus !== "no_booking") {
                        return $this->getMailingOccasions($entity, $owner);
                    }
                    return [];
                }
                throw new \InvalidArgumentException("Unexpected component type '$component_type'");
            }
        }
        return array();
    }

    /**
     * Check user has permissions to view and book_by_this on the owner
     *
     * @param int 	$owner_ref_id
     *
     * @return bool
     */
    protected function checkPermissionsOn(int $owner_ref_id)
    {
        if (\ilContext::getType() === \ilContext::CONTEXT_CRON) {
            return true;
        }
        global $DIC;
        $g_access = $DIC->access();
        return $g_access->checkAccess("book_by_this", "", $owner_ref_id);
    }

    /**
     * Returns components for automatic mails
     *
     * @return MailingOccasion[]
     */
    protected function getMailingOccasions($entity, $owner)
    {
        $path = $owner->getPluginDirectory() . '/classes/MailingOccasions';
        $no_classes = array('MailOccasionBase.php', '.', '..');
        $files = array_diff(scandir($path), $no_classes);
        $ret = array();
        foreach ($files as $filename) {
            $classname = 'CaT\Plugins\BookingModalities\MailingOccasions\\'
                . str_replace('.php', '', $filename);
            $ret[] = new $classname($entity, $this->txt);
        }
        return $ret;
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

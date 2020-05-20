<?php
namespace CaT\Plugins\BookingModalities\Mailing;

require_once('./Services/TMS/Mailing/classes/class.ilTMSMailContextEnte.php');
require_once('./Services/TMS/Mailing/classes/class.ilTMSMailContextUser.php');

/**
 * Provide placeholders in the context of accomodations
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class MailContextBookingModalities extends \ilTMSMailContextEnte
{
    protected static $PLACEHOLDERS = array(
        'BOOKING_LINK' => 'placeholder_desc_booking_link'
    );

    /**
     * @inheritdoc
     */
    public function placeholderIds()
    {
        return array_keys(self::$PLACEHOLDERS);
    }

    /**
     * @inheritdoc
     */
    public function placeholderDescriptionForId(string $placeholder_id) : string
    {
        $desc = self::$PLACEHOLDERS[$placeholder_id];
        $obj = \ilPluginAdmin::getPluginObjectById("xbkm");
        return $obj->txt($desc);
    }

    /**
     * @inheritdoc
     */
    public function valueFor(string $placeholder_id, array $contexts = array()) : ?string
    {
        if (!in_array($placeholder_id, $this->placeholderIds())) {
            return null;
        }
        return $this->resolveValueFor($placeholder_id, $contexts);
    }

    /**
     * @param string $id
     * @param MailContexts[] $contexts
     * @return $string | null
     */
    protected function resolveValueFor($id, $contexts)
    {
        switch ($id) {
            case 'BOOKING_LINK':
                require_once("Services/Link/classes/class.ilLink.php");
                $owner = $this->owner();
                $parent = $owner->getParentCourse();

                if (is_null($parent)) {
                    return null;
                }

                require_once("Services/Link/classes/class.ilLink.php");
                return \ilLink::_getStaticLink($owner->getRefId(), 'xbkm', true, "_crs" . $parent->getRefId());
            default:
                return 'NOT RESOLVED: ' . $id;
        }
    }
}

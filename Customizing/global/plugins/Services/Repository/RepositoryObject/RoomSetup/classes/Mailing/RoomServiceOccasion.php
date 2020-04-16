<?php
namespace CaT\Plugins\RoomSetup\Mailing;

use CaT\Plugins\RoomSetup\Settings\RoomSetup as Setting;

/**
 * Occasion for sending the RoomSetup's Service part
 * The event will be deferred, i.e. issued by ScheduledEvents.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class RoomServiceOccasion extends BaseOccasion
{
    const TEMPLATE_IDENT = 'O03';

    protected static $events = array(
        self::EVENT_SEND_SERVICE
    );

    /**
     * @inheritdoc
     */
    protected function getRecipient()
    {
        $which = Setting::TYPE_SERVICE;
        $mail = $this->owner->getEffectiveMailRecipient($which);
        if (is_null($mail)) {
            return null;
        }
        $recipient = new \ilTMSMailRecipient();
        $recipient = $recipient->withMail($mail);
        return $recipient;
    }

    /**
     * @inheritdoc
     */
    public function getNextScheduledDate()
    {
        $due_dates = $this->owner()->getDueDates();
        return $due_dates[Setting::TYPE_SERVICE];
    }

    /**
     * @inheritdoc
     */
    protected function existValuesForMailing()
    {
        $equipment = $this->owner->getActions()->getEquipment();
        if (
            count($equipment->getServiceOptions()) > 0 ||
            trim($equipment->getSpecialWishes()) !== ''
        ) {
            return true;
        }
        return false;
    }
}

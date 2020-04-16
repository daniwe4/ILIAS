<?php
namespace CaT\Plugins\RoomSetup\Mailing;

use CaT\Plugins\RoomSetup\Settings\RoomSetup as Setting;

/**
 * Occasion for sending the RoomSetup
 * The event will be deferred, i.e. issued by ScheduledEvents.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class RoomSetupOccasion extends BaseOccasion
{
    const TEMPLATE_IDENT = 'O02';

    protected static $events = array(
        self::EVENT_SEND_ROOMSETUP
    );

    /**
     * @inheritdoc
     */
    protected function getRecipient()
    {
        $which = Setting::TYPE_ROOMSETUP;
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
        return $due_dates[Setting::TYPE_ROOMSETUP];
    }

    /**
     * @inheritdoc
     */
    protected function existValuesForMailing()
    {
        $equipment = $this->owner->getActions()->getEquipment();
        if (
            trim($equipment->getRoomInformation()) !== '' ||
            trim($equipment->getSeatOrder()) !== ''
        ) {
            return true;
        }
        return false;
    }
}

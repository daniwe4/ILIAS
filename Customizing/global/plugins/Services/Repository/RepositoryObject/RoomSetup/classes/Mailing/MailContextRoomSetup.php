<?php
namespace CaT\Plugins\RoomSetup\Mailing;

require_once('./Services/TMS/Mailing/classes/class.ilTMSMailContextEnte.php');
require_once('./Services/TMS/Mailing/classes/class.ilTMSMailContextUser.php');

/**
 * Mailcontext for RoomSetup
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class MailContextRoomSetup extends \ilTMSMailContextEnte
{
    protected static $PLACEHOLDERS = array(
        'ROOM_SERVICE_OPTIONS' => 'placeholder_desc_room_service_options',
        'ROOM_SERVICE_SPECIALS' => 'placeholder_desc_room_service_specials',
        'ROOM_SETUP_INFO' => 'placeholder_desc_room_setup_info',
        'ROOM_SETUP_SEATORDER' => 'placeholder_desc_room_setup_seatorder'
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
        $obj = \ilPluginAdmin::getPluginObjectById("xrse");
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
        $actions = $this->owner->getActions();
        $equipment = $actions->getEquipment();

        switch ($id) {
            case 'ROOM_SERVICE_OPTIONS':
                $options = $equipment->getServiceOptions();
                $plug = \ilPluginAdmin::getPluginObjectById("xrse");
                $plug_actions = $plug->getActions();
                $option_values = [];
                foreach ($options as $option) {
                    $so = $plug_actions->getServiceOptionById((int) $option);
                    $option_values[] = $so->getName();
                }
                return implode('<br \>', $option_values);

            case 'ROOM_SERVICE_SPECIALS':
                return $equipment->getSpecialWishes();

            case 'ROOM_SETUP_INFO':
                return $equipment->getRoomInformation();

            case 'ROOM_SETUP_SEATORDER':
                return $equipment->getSeatOrder();

            default:
                return 'NOT RESOLVED: ' . $id;
        }
    }
}

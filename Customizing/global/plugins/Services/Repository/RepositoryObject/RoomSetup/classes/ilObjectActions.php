<?php

namespace CaT\Plugins\RoomSetup;

class ilObjectActions
{
    const F_SETTINGS_TITLE = "title";
    const F_SETTINGS_DESCRIPTION = "description";

    const F_SERVICE_OPTIONS = "service_options";
    const F_SPECIAL_WISHES = "special_wishes";
    const F_ROOM_INFORMATION = "room_information";
    const F_SEAT_ORDER = "seat_order";

    const F_SERVICE_RECIPIENT_MODE = "service_recipient_mode";
    const F_SERVICE_RECIPIENT = "service_recipient";
    const F_SERVICE_SEND_DAYS_BEFORE = "service_send_days_before";

    const F_SETUP_RECIPIENT_MODE = "setup_recipient_mode";
    const F_SETUP_RECIPIENT = "setup_recipient";
    const F_SETUP_SEND_DAYS_BEFORE = "setup_send_days_before";



    const M_COURSE_VENUE = "venue_config";
    const M_SELECTION = "selection";

    public function __construct(\ilObjRoomSetup $object, Equipment\DB $equipment_db, \ilAppEventHandler $app_event_handler, Settings\DB $settings_db)
    {
        $this->object = $object;
        $this->equipment_db = $equipment_db;
        $this->app_event_handler = $app_event_handler;
        $this->settings_db = $settings_db;
    }

    /**
     * Get the object
     *
     * @return \ilObjRoomSetup
     */
    public function getObject()
    {
        if ($this->object === null) {
            throw new \LogicException(__METHOD__ . " object was not set");
        }

        return $this->object;
    }

    /**
     * Create new equipment entry
     *
     * @param int[] | [] 	$service_options
     * @param string 		$special_wishes
     * @param string 		$room_information
     * @param string 		$seat_order
     *
     * @return Equipment\Equipment
     */
    public function createNewEquipment(
        array $service_options,
        string $special_wishes,
        string $room_information,
        string $seat_order
    ) {
        $this->equipment_db->create((int) $this->getObject()->getID(), $service_options, $special_wishes, $room_information, $seat_order);
    }

    /**
     * Get equipment for current object
     *
     * @return Equipment
     */
    public function getEquipment()
    {
        return $this->equipment_db->selectFor((int) $this->getObject()->getId());
    }

    /**
     * Get values for setting form
     *
     * @return string[]
     */
    public function getSettingsValues()
    {
        $values = array();
        $obj = $this->getObject();
        $values[self::F_SETTINGS_TITLE] = $obj->getTitle();
        $values[self::F_SETTINGS_DESCRIPTION] = $obj->getDescription();

        $settings = $obj->getSettings();

        foreach ($settings as $setting) {
            $recipient_mode = $setting->getRecipientMode();
            if ($recipient_mode === null) {
                $recipient_mode = self::M_COURSE_VENUE;
            }

            switch ($setting->getType()) {
                case Settings\RoomSetup::TYPE_SERVICE:
                        $values[self::F_SERVICE_RECIPIENT_MODE] = $recipient_mode;
                        $values[self::F_SERVICE_RECIPIENT] = $setting->getRecipient();
                        $values[self::F_SERVICE_SEND_DAYS_BEFORE] = $setting->getSendDaysBefore();

                    break;
                case Settings\RoomSetup::TYPE_ROOMSETUP:
                        $values[self::F_SETUP_RECIPIENT_MODE] = $recipient_mode;
                        $values[self::F_SETUP_RECIPIENT] = $setting->getRecipient();
                        $values[self::F_SETUP_SEND_DAYS_BEFORE] = $setting->getSendDaysBefore();

                    break;
                default:
                    throw new \Exception("no type for setting", 1);
            }
        }

        return $values;
    }

    /**
     * Get equipment values of room setup for edit form
     *
     * @return array<string, string>
     */
    public function getFormValues()
    {
        $equipment = $this->equipment_db->selectFor((int) $this->getObject()->getId());

        $values = array();
        $values[self::F_SERVICE_OPTIONS] = $equipment->getServiceOptions();
        $values[self::F_SPECIAL_WISHES] = $equipment->getSpecialWishes();
        $values[self::F_ROOM_INFORMATION] = $equipment->getRoomInformation();
        $values[self::F_SEAT_ORDER] = $equipment->getSeatOrder();

        return $values;
    }

    /**
     * Create default settings
     *
     * @return Settings\RoomSetup[]
     */
    public function create()
    {
        return $this->settings_db->create((int) $this->getObject()->getId(), self::M_COURSE_VENUE);
    }

    /**
     * Update current settings
     *
     * @param Settings\RoomSetup[] 	$room_setups
     *
     * @return void
     */
    public function update(array $room_setups)
    {
        foreach ($room_setups as $room_setup) {
            if (!is_a($room_setup, Settings\RoomSetup::class)) {
                throw new \InvalidArgumentException("Wrong type", 1);
            }
            $this->settings_db->update($room_setup);
        }
    }

    /**
     * Get the settings
     *
     * @return Settings\RoomSetup[]
     */
    public function select()
    {
        return $this->settings_db->selectFor((int) $this->getObject()->getId());
    }

    /**
     * Update settings of room setup
     *
     * @param string[] 		$values
     *
     * @return null
     */
    public function updateSettings(array $values)
    {
        $obj = $this->getObject();

        $obj->setTitle($values[self::F_SETTINGS_TITLE]);
        $obj->setDescription($values[self::F_SETTINGS_DESCRIPTION]);

        $service_recipient_mode = $values[self::F_SERVICE_RECIPIENT_MODE];
        $service_recipient = $values[self::F_SERVICE_RECIPIENT];
        $service_send_days_before = $values[self::F_SERVICE_SEND_DAYS_BEFORE];

        if ($service_recipient_mode == self::M_COURSE_VENUE) {
            $service_recipient = null;
            $service_send_days_before = null;
        } else {
            $service_send_days_before = (int) $service_send_days_before;
        }

        $setup_recipient_mode = $values[self::F_SETUP_RECIPIENT_MODE];
        $setup_recipient = $values[self::F_SETUP_RECIPIENT];
        $setup_send_days_before = $values[self::F_SETUP_SEND_DAYS_BEFORE];

        if ($setup_recipient_mode == self::M_COURSE_VENUE) {
            $setup_recipient = null;
            $setup_send_days_before = null;
        } else {
            $setup_send_days_before = (int) $setup_send_days_before;
        }


        $params = [];
        $params[Settings\RoomSetup::TYPE_SERVICE] = array(
            $service_recipient_mode,
            $service_recipient,
            $service_send_days_before
        );
        $params[Settings\RoomSetup::TYPE_ROOMSETUP] = array(
            $setup_recipient_mode,
            $setup_recipient,
            $setup_send_days_before
        );

        $obj->updateSettings(function ($settings) use ($params) {
            $ret = [];
            foreach ($settings as $s) {
                list($recipient_mode, $recipient, $send_days_before) = $params[$s->getType()];
                $ret[] = $s
                    ->withRecipientMode($recipient_mode)
                    ->withRecipient($recipient)
                    ->withSendDaysBefore($send_days_before)
                ;
            }
            return $ret;
        });

        $obj->update();
    }

    /**
     * Get an equipment object
     *
     * @param int[] 	$service_options
     * @param string 	$special_wishes
     * @param string 	$room_information
     * @param string 	$seat_order
     *
     * @return Equipment\Equipment
     */
    public function getEquipmentWith(array $service_options, $special_wishes, $room_information, $seat_order)
    {
        $equipment = $this->equipment_db->selectFor((int) $this->getObject()->getId());
        return $equipment
            ->withServiceOptions($service_options)
            ->withSpecialWishes($special_wishes)
            ->withRoomInformation($room_information)
            ->withSeatOrder($seat_order);
    }

    /**
     * Update existing equipment for room set
     *
     * @param Equipment\Equipment 	$equipment
     *
     * @return null
     */
    public function updateEquipment(Equipment\Equipment $equipment)
    {
        $this->equipment_db->update($equipment);
    }

    public function deallocateAllServiceOptions()
    {
        $this->equipment_db->deallocateAllServiceOptions((int) $this->getObject()->getId());
    }

    /**
     * Delete equipment for room setup
     *
     * @return null
     */
    public function deleteEquipment()
    {
        $this->equipment_db->deleteFor((int) $this->getObject()->getId());
    }

    /**
     * Raise event if room setup is deleted
     *
     * @return null
     */
    public function raiseDelete($crs_ref_id)
    {
        if ($crs_ref_id !== null) {
            global $DIC;
            $log = $DIC->logger()->root();
            $log->write("Raise event delete for RoomSetup");
            $this->app_event_handler->raise("Plugins/RoomSetup", "delete", array(
                "crs_ref_id" => $crs_ref_id,
                "eqp_ref_id" => $this->getObject()->getRefid()
                ));
        }
    }

    public function getParentRefIdFromTree()
    {
        return $this->equipment_db->getParentRefIdFromTree($this->getObject()->getRefId());
    }


    /**
     * delete settings for object from Settings-db
     *
     * @return null
     */
    public function deleteAllSettings()
    {
        $this->settings_db->deleteFor((int) $this->getObject()->getId());
    }
}

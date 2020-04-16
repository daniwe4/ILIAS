<?php
include_once("Services/Repository/classes/class.ilObjectPlugin.php");

use \CaT\Plugins\RoomSetup;
use \CaT\Plugins\RoomSetup\Mailing as RSMails;
use CaT\Ente\ILIAS\ilProviderObjectHelper;

/**
 * Object of the plugin
 */
class ilObjRoomSetup extends \ilObjectPlugin implements RoomSetup\ObjRoomSetup
{
    use RSMails\ScheduledEvents;
    use ilProviderObjectHelper;

    /**
     * @var RoomSetup[]
     */
    private $settings;

    /**
     * Init the type of the plugin. Same value as choosen in plugin.php
     */
    public function initType()
    {
        $this->setType("xrse");
    }

    /**
     * @inheritdoc
     */
    protected function getDIC()
    {
        return $GLOBALS["DIC"];
    }

    /**
     * @inheritdoc
     */
    public function doCreate()
    {
        $this->settings = $this->getActions()->create();
        $this->getActions()->createNewEquipment(array(), "", "", "");

        $this->createUnboundProvider("crs", CaT\Plugins\RoomSetup\UnboundProvider::class, __DIR__ . "/UnboundProvider.php");
        $this->createUnboundProvider("crs", CaT\Plugins\RoomSetup\SharedUnboundProvider::class, __DIR__ . "/SharedUnboundProvider.php");
    }

    /**
     * Get called if the object get be updated
     * Update additoinal setting values
     */
    public function doUpdate()
    {
        $this->getActions()->update($this->getSettings());
        $this->scheduleMailingEvents();
    }

    /**
     * Get called after object creation to read further information
     */
    public function doRead()
    {
        $this->settings = $this->getActions()->select();
    }

    protected function beforeDelete()
    {
        $this->crs_ref_id = $this->getActions()->getParentRefIdFromTree();
        return true;
    }

    /**
     * Get called if the object should be deleted.
     * Delete additional settings
     */
    public function doDelete()
    {
        $this->getActions()->deallocateAllServiceOptions();
        $this->getActions()->deleteEquipment();

        $this->deleteExistingMailingEvents();
        $this->getActions()->deleteAllSettings();

        if ($this->crs_ref_id !== null) {
            $this->getActions()->raiseDelete($this->crs_ref_id);
        }
    }

    /**
     * Get called if the object get be copied.
     * Copy additional settings to new object
     */
    public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
    {
        $this->cloneSettings($new_obj);
        $this->cloneEquipment($new_obj);
        $new_obj->update();
    }

    protected function cloneSettings($new_obj)
    {
        $n_settings = $new_obj->getSettings();
        $settings = $this->getSettings();

        $updated_settings = [];

        foreach ($settings as $setting) {
            //find corresponding setting (by type) in new settings
            foreach ($n_settings as $n_setting) {
                if ($n_setting->getType() === $setting->getType()) {
                    $n_setting = $n_setting
                        ->withRecipientMode($setting->getRecipientMode())
                        ->withRecipient($setting->getRecipient())
                        ->withSendDaysBefore($setting->getSendDaysBefore());
                    $updated_settings[] = $n_setting;
                }
            }
        }

        $new_obj->setSettings($updated_settings);
    }

    protected function cloneEquipment($new_obj)
    {
        $n_actions = $new_obj->getActions();
        $actions = $this->getActions();
        $equipment = $actions->getEquipment();

        $n_equipment = $n_actions->getEquipmentWith(
            $equipment->getServiceOptions(),
            $equipment->getSpecialWishes(),
            $equipment->getRoomInformation(),
            $equipment->getSeatOrder()
        );

        $n_actions->updateEquipment($n_equipment);
    }

    /**
     * @inheritdoc
     */
    public function updateSettings(\Closure $update)
    {
        $this->settings = $update($this->getSettings());
    }

    /**
     * Get actions of this object
     *
     * @return RoomSetup\ilObjectActions
     */
    public function getActions()
    {
        if ($this->actions === null) {
            global $DIC;
            $db = $DIC->database();
            $app_event_handler = $DIC["ilAppEventHandler"];

            $this->actions = new RoomSetup\ilObjectActions($this, $this->getEquipmentDB($db), $app_event_handler, $this->getSettingsDB($db));
        }

        return $this->actions;
    }

    /**
     * Get equipment db
     *
     * @param $db
     *
     * @return RoomSetup\Equipment\DB
     */
    public function getEquipmentDB($db)
    {
        if ($this->equipment_db === null) {
            $this->equipment_db = new RoomSetup\Equipment\ilDB($db);
        }

        return $this->equipment_db;
    }

    /**
     * Get settings db
     *
     * @param $db
     *
     * @return RoomSetup\Settings\DB
     */
    public function getSettingsDB($db)
    {
        if ($this->settings_db === null) {
            $this->settings_db = new RoomSetup\Settings\ilDB($db);
        }

        return $this->settings_db;
    }

    /**
     * @inheritdoc
     */
    public function setSettings(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @inheritdoc
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Get object of parent course
     *
     * @return \ilObjCourse
     */
    public function getParentCourse()
    {
        global $DIC;
        $tree = $DIC->repositoryTree();

        $parents = $tree->getPathFull($this->getRefId());
        $parent = array_filter($parents, function ($p) {
            if ($p["type"] == "crs") {
                return $p;
            }
        });

        if (count($parent) > 0) {
            $parent_crs = array_shift($parent);
            require_once("Services/Object/classes/class.ilObjectFactory.php");
            return ilObjectFactory::getInstanceByRefId($parent_crs["ref_id"]);
        }
        return null;
    }

    /**
     * Closure to get txt from plugin
     */
    public function txtClosure()
    {
        return function ($code) {
            return $this->txt($code);
        };
    }
}

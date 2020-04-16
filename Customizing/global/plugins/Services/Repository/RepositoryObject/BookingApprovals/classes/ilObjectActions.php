<?php

declare(strict_types=1);

namespace CaT\Plugins\BookingApprovals;

/**
 * Communication class between front- and backend.
 * E.g. GUI only use this class to get information from ILIAS DB.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class ilObjectActions
{
    /**
     * @var \ilObjBookingApprovals
     */
    protected $object;

    public function __construct(\ilObjBookingApprovals $object)
    {
        $this->object = $object;
    }

    /**
     * @throws \LogicException
     */
    public function getObject() : \ilObjBookingApprovals
    {
        if ($this->object === null) {
            throw new \LogicException("No object was set");
        }
        return $this->object;
    }

    public function getSettingsValues() : array
    {
        require_once __DIR__ . "/Settings/class.ilBookingApprovalsSettingsGUI.php";

        $obj = $this->getObject();

        $ret = array();
        $ret[\ilBookingApprovalsSettingsGUI::F_SETTINGS_TITLE] = $obj->getTitle();
        $ret[\ilBookingApprovalsSettingsGUI::F_SETTINGS_DESCRIPTION] = $obj->getDescription();
        $ret[\ilBookingApprovalsSettingsGUI::F_SETTINGS_SUPERIOR_VIEW] =
            $obj->getSettings()->getSuperiorView();

        return $ret;
    }

    public function updateSettings(array $post)
    {
        require_once __DIR__ . "/Settings/class.ilBookingApprovalsSettingsGUI.php";

        $obj = $this->getObject();
        $obj->setTitle($post[\ilBookingApprovalsSettingsGUI::F_SETTINGS_TITLE]);
        $obj->setDescription($post[\ilBookingApprovalsSettingsGUI::F_SETTINGS_DESCRIPTION]);
        $superior_view = (bool) $post[\ilBookingApprovalsSettingsGUI::F_SETTINGS_SUPERIOR_VIEW];

        $fnc = function ($s) use ($superior_view) {
            return $s->withSuperiorView($superior_view);
        };
        $obj->updateSettings($fnc);
        $obj->update();
    }


    public function getSettingsDB() : Settings\DB
    {
        return $this->getObject()->getSettingsDB();
    }

    public function createEmptySettings() : Settings\BookingApprovals
    {
        return $this->getSettingsDB()
            ->create((int) $this->getObject()->getId());
    }

    public function deleteSettings()
    {
        $this->getSettingsDB()
            ->deleteFor((int) $this->getObject()->getId());
    }

    public function getSettings() : Settings\BookingApprovals
    {
        return $this->getSettingsDB()
            ->selectFor((int) $this->getObject()->getId());
    }

    public function dbUpdateSettings(Settings\BookingApprovals $settings)
    {
        $this->getSettingsDB()
            ->update($settings);
    }
}

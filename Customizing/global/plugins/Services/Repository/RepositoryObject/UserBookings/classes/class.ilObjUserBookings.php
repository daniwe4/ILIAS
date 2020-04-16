<?php
include_once("Services/Repository/classes/class.ilObjectPlugin.php");

use CaT\Plugins\UserBookings;
use CaT\Ente\ILIAS\ilProviderObjectHelper;

/**
 * Object of the plugin
 */
class ilObjUserBookings extends ilObjectPlugin
{
    use ilProviderObjectHelper;

    public function __construct($a_ref_id = 0)
    {
        parent::__construct($a_ref_id);

        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->g_user = $DIC->user();
        $this->g_access = $DIC->access();
    }

    /**
     * Init the type of the plugin. Same value as choosen in plugin.php
     */
    public function initType()
    {
        $this->setType("xubk");
    }

    /**
     * Creates ente-provider.
     */
    public function doCreate()
    {
        $this->settings = $this->getSettingsDB()->create((int) $this->getId());
        $this->createUnboundProvider("root", UserBookings\UnboundProvider::class, __DIR__ . "/UnboundProvider.php");
    }

    /**
     * Get called if the object get be updated
     * Update additoinal setting values
     */
    public function doUpdate()
    {
        $this->getActions()->update($this->settings);
    }

    /**
     * Get called after object creation to read further information
     */
    public function doRead()
    {
        $this->settings = $this->getActions()->select();
    }

    /**
     * Get called if the object should be deleted.
     * Delete additional settings
     */
    public function doDelete()
    {
        $this->getActions()->delete();
        $this->deleteUnboundProviders();
    }

    /**
     * Get called if the object get be coppied.
     * Copy additional settings to new object
     */
    public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
    {
        $fnc = function ($s) {
            return $s->withSuperiorView($this->settings->getSuperiorView());
        };

        $new_obj->updateSettings($fnc);
        $new_obj->update();
    }

    /**
     * Get the actions of object
     *
     * @return ilObjActions
     */
    public function getActions()
    {
        if ($this->actions === null) {
            $this->actions = new UserBookings\ilObjActions(
                $this,
                $this->getBookingDB(),
                $this->getSettingsDB(),
                $this->getSuperiorViewDB()
            );
        }

        return $this->actions;
    }

    /**
     * Get instance of booking db
     *
     * @return UserBookings\UserBooking\DB
     */
    protected function getBookingDB()
    {
        if ($this->booking_db === null) {
            global $DIC;
            $db = $DIC->database();
            $rbacreview = $DIC->rbac()->review();
            $tree = $DIC->repositoryTree();
            $access = $DIC->access();
            $helper = new UserBookings\Helper();
            $this->booking_db = new UserBookings\UserBooking\ilDB(
                $db,
                $rbacreview,
                $tree,
                $helper,
                $access,
                $this->getSettingsDB()->selectFor((int) $this->getId()),
                $this,
                $this->getTreeObjDiscovery()
            );
        }

        return $this->booking_db;
    }

    /**
     * Get instance of settings db
     *
     * @return UserBookings\Settings\DB
     */
    protected function getSettingsDB()
    {
        if ($this->settings_db === null) {
            global $DIC;
            $db = $DIC->database();
            $this->settings_db = new UserBookings\Settings\ilDB($db);
        }

        return $this->settings_db;
    }

    /**
     * Get instance of superior view db
     *
     * @return UserBookings\SuperiorView\DB
     */
    protected function getSuperiorViewDB()
    {
        if ($this->superior_view_db === null) {
            global $DIC;
            $db = $DIC->database();
            $rbacreview = $DIC->rbac()->review();
            $tree = $DIC->repositoryTree();
            $helper = new UserBookings\Helper();
            $this->superior_view_db = new UserBookings\SuperiorView\ilDB(
                $db,
                $rbacreview,
                $tree,
                $helper,
                $this->getSettingsDB()->selectFor((int) $this->getId()),
                $this,
                $this->getTreeObjDiscovery()
            );
        }

        return $this->superior_view_db;
    }

    /**
     * Get current settings
     *
     * @return UserBookings\Settings\UserBookingsSettings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Updates the settings
     *
     * @param \Closure 	$fnc
     */
    public function updateSettings(\Closure $fnc)
    {
        $this->settings = $fnc($this->settings);
    }

    /**
     * Get the values provided by this object.
     *
     * @return	string[]
     */
    public function getProvidedValues()
    {
        $returns = array();

        if ($this->getSettings()->getSuperiorView() && $this->checkSuperior() && $this->checkAccess()) {
            require_once(__DIR__ . "/SuperiorView/class.ilSuperiorViewGUI.php");
            $this->g_ctrl->setParameterByClass("ilSuperiorViewGUI", "ref_id", $this->getRefId());
            $link = $this->g_ctrl->getLinkTargetByClass(array("ilObjPluginDispatchGUI", "ilObjUserBookingsGUI", "ilSuperiorViewGUI"), ilSuperiorViewGUI::CMD_SHOW_BOOKINGS);
            $this->g_ctrl->setParameterByClass("ilSuperiorViewGUI", "ref_id", null);

            $returns[] = ["title" => $this->getTitle(),
                "tooltip" => $this->getDescription(),
                "link" => $link,
                "icon_path" => $this->getIconPath(true),
                "active_icon_path" => $this->getActiceIconPath(true),
                "identifier" => $this->getRefId()
            ];
        } elseif (!$this->getSettings()->getSuperiorView() && $this->checkAccess()) {
            $this->g_ctrl->setParameterByClass("iluserbookingsgui", "ref_id", $this->getRefId());
            $link = $this->g_ctrl->getLinkTargetByClass(array("ilObjPluginDispatchGUI", "ilObjUserBookingsGUI", "iluserbookingsgui"), "showBookings");
            $this->g_ctrl->setParameterByClass("iluserbookingsgui", "ref_id", null);

            $returns[] = ["title" => $this->getTitle(),
                "tooltip" => $this->getDescription(),
                "link" => $link,
                "icon_path" => $this->getIconPath(),
                "active_icon_path" => $this->getActiceIconPath(),
                "identifier" => $this->getRefId()
            ];
        }

        return $returns;
    }

    /**
     * Checks the current user is superior in at least on org unit
     *
     * @return bool
     */
    protected function checkSuperior()
    {
        require_once("Services/TMS/Positions/TMSPositionHelper.php");
        require_once("Modules/OrgUnit/classes/Positions/UserAssignment/class.ilOrgUnitUserAssignmentQueries.php");
        $tms_pos_helper = new TMSPositionHelper(ilOrgUnitUserAssignmentQueries::getInstance());
        $orgus = $tms_pos_helper->getOrgUnitIdsWhereUserHasAuthority((int) $this->g_user->getId());

        return count($orgus) > 0;
    }

    /**
     * Checks access to object
     *
     * @return bool
     */
    protected function checkAccess()
    {
        return $this->g_access->checkAccess('visible', '', $this->getRefId())
            && $this->g_access->checkAccess('read', '', $this->getRefId());
    }

    /**
     * Get Path of default icon
     *
     * @param bool 	$superior_icon
     *
     * @return string
     */
    protected function getIconPath($superior_icon = false)
    {
        if ($superior_icon) {
            return $this->getPlugin()->getImagePath("icon_xubk_sup.svg");
        } else {
            return $this->getPlugin()->getImagePath("icon_xubk.svg");
        }
    }

    /**
     * Get Path of active icon
     *
     * @param bool 	$superior_icon
     *
     * @return string
     */
    protected function getActiceIconPath($superior_icon = false)
    {
        if ($superior_icon) {
            return $this->getPlugin()->getImagePath("icon_xubk_sup_active.svg");
        } else {
            return $this->getPlugin()->getImagePath("icon_xubk_active.svg");
        }
    }

    /**
     * Translate lang code into value
     *
     * @param string 	$code
     *
     * @return string
     */
    public function pluginTxt($code)
    {
        return parent::txt($code);
    }

    /**
     * @inheritdoc
     */
    protected function getDIC()
    {
        global $DIC;
        return $DIC;
    }

    protected function getTreeObjDiscovery()
    {
        return new ilTreeObjectDiscovery($this->getDIC()->repositoryTree());
    }
}

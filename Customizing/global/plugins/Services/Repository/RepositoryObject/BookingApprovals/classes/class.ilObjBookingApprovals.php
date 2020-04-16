<?php
include_once("Services/Repository/classes/class.ilObjectPlugin.php");

use CaT\Plugins\BookingApprovals;
use CaT\Plugins\BookingApprovals\ilObjectActions;
use CaT\Ente\ILIAS\ilProviderObjectHelper;
use CaT\Plugins\BookingApprovals\Settings;

/**
 * Object of the plugin.
 *
 * @author
 * @copyright Extended GPL, see LICENSE
 */
class ilObjBookingApprovals extends ilObjectPlugin implements BookingApprovals\ObjBookingApprovals
{
    use ilProviderObjectHelper;

    /**
     * @var ilObjectActions
     */
    protected $object_actions;

    public function __construct($a_ref_id = 0)
    {
        parent::__construct($a_ref_id);

        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->g_user = $DIC->user();
        $this->g_access = $DIC->access();
    }

    /**
     * @var Settings\DB
     */
    protected $settings_db;
    /**
     * @var Settings\BookingApprovals
     */
    protected $settings;

    /**
     * Gets called if the object is copied.
     * Copy additional settings to new object.
     *
     * @return 	void
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
     * Create settings or provider objects.
     *
     * @return 	void
     */
    public function doCreate()
    {
        $this->settings = $this->getObjectActions()->createEmptySettings();
        $this->createUnboundProvider("root", BookingApprovals\UnboundProvider::class, __DIR__ . "/UnboundProvider.php");
        $this->createUnboundProvider("crs", BookingApprovals\UnboundProvider::class, __DIR__ . "/UnboundProvider.php");
    }

    /**
     * Gets called if the object is about to be deleted.
     * Delete additional settings.
     *
     * @return 	void
     */
    public function doDelete()
    {
        $this->getObjectActions()->deleteSettings();
    }

    /**
     * Get called after object creation to read further information.
     *
     * @return 	void
     */
    public function doRead()
    {
        $this->settings = $this->getObjectActions()->getSettings();
    }

    /**
     * Get called if the object get be updated.
     * Update additoinal setting values.
     *
     * @return 	void
     */
    public function doUpdate()
    {
        $this->getObjectActions()->dbUpdateSettings($this->settings);
    }

    /**
     * Get the directory of this plugin
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->plugin->getDirectory();
    }

    /**
     * Get the object actions.
     *
     * @return 	ilObjectActions
     */
    public function getObjectActions()
    {
        if ($this->object_actions == null) {
            $this->object_actions = new ilObjectActions($this);
        }
        return $this->object_actions;
    }

    /**
     * Get the first parent object in tree for given types.
     *
     * @return 	ilObject|null
     */
    public function getFirstParent()
    {
        global $DIC;
        $tree = $DIC->repositoryTree();

        $ref_id = $this->getRefId();
        if ($ref_id === null) {
            $ref_id = $_GET["ref_id"];
        }
        $parents = $tree->getPathFull($ref_id);
        $parents = array_filter($parents, function ($p) {
            if (in_array($p["type"], $this->getParentTypes())) {
                return $p;
            }
        });

        if (count($parents) > 0) {
            return array_pop($parents);
        }
        return null;
    }

    /**
     * Get settings.
     *
     * @return 	void
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Update Settings.
     *
     * @param 	\Closure 	$c
     * @return 	void
     */
    public function updateSettings(\Closure $c)
    {
        $this->settings = $c($this->settings);
    }

    /**
     * Init the type of the plugin. Same value as choosen in plugin.php
     */
    public function initType()
    {
        $this->setType("xbka");
    }

    /**
     * Get a closure to get txts from plugin.
     *
     * @return \Closure
     */
    public function txtClosure()
    {
        return function ($code) {
            return $this->txt($code);
        };
    }

    /**
     * Get the parent course
     *
     * @return ilObjCourse | null
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
     * Get the values provided by this object.
     *
     * @return	string[]
     */
    public function getProvidedValues()
    {
        $returns = array();
        if ($this->getSettings()->getSuperiorView() && $this->checkSuperior() && $this->checkAccess()) {
            $this->g_ctrl->setParameterByClass("ilApprovalsOverviewGUI", "ref_id", $this->getRefId());
            $link = $this->g_ctrl->getLinkTargetByClass(
                array("ilObjPluginDispatchGUI", "ilObjBookingApprovalsGUI", "ilApprovalsOverviewGUI"),
                BookingApprovals\Approvals\ApprovalGUI::CMD_SHOW_OVERVIEW
            );

            $returns[] = ["title" => $this->getTitle(),
                "tooltip" => $this->getDescription(),
                "link" => $link,
                "icon_path" => $this->getIconPath(true),
                "active_icon_path" => $this->getActiceIconPath(true),
                "identifier" => $this->getRefId()
            ];
        } elseif (!$this->getSettings()->getSuperiorView() && $this->checkAccess()) {
            $this->g_ctrl->setParameterByClass("ilMyApprovalsOverviewGUI", "ref_id", $this->getRefId());
            $link = $this->g_ctrl->getLinkTargetByClass(
                array("ilObjPluginDispatchGUI", "ilObjBookingApprovalsGUI", "ilMyApprovalsOverviewGUI"),
                BookingApprovals\Approvals\ApprovalGUI::CMD_SHOW_MY_APPROVALS
            );

            $returns[] = ["title" => $this->getTitle(),
                "tooltip" => $this->getDescription(),
                "link" => $link,
                "icon_path" => $this->getIconPath(true),
                "active_icon_path" => $this->getActiceIconPath(true),
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
    public function checkSuperior()
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
    public function checkAccess()
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
            return $this->getPlugin()->getImagePath("icon_xbka.svg");
        } else {
            return $this->getPlugin()->getImagePath("icon_xbka.svg");
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
            return $this->getPlugin()->getImagePath("icon_xbka.svg");
        } else {
            return $this->getPlugin()->getImagePath("icon_xbka.svg");
        }
    }

    /**
     * @inheritdoc
     */
    protected function getDIC()
    {
        global $DIC;
        return $DIC;
    }

    /*
     * Get instance of settings db
     */
    public function getSettingsDB() : Settings\DB
    {
        if ($this->settings_db === null) {
            global $DIC;
            $db = $DIC->database();
            $this->settings_db = new Settings\ilDB($db);
        }
        return $this->settings_db;
    }

    public function getPluginObject()
    {
        return $this->plugin;
    }

    /**
     * @return Booking\Actions
     */
    public function getBookingActions()
    {
        require_once("Services/TMS/Booking/classes/class.ilTMSBookingActions.php");
        return new ilTMSBookingActions();
    }
}

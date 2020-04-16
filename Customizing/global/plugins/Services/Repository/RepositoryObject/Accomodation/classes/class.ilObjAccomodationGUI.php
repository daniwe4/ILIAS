<?php
include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");

require_once(__DIR__ . '/ObjSettings/class.ilObjSettingsGUI.php');
require_once(__DIR__ . '/Reservation/class.ilReservationGUI.php');
require_once(__DIR__ . '/Reservation/class.ilUserReservationsGUI.php');

use \CaT\Plugins\Accomodation;
use \CaT\Plugins\Accomodation\TableProcessing\TableProcessor;

/**
 * Plugin object GUI class. Baseclass for all GUI action in ILIAS
 *
 * @ilCtrl_isCalledBy ilObjAccomodationGUI: ilRepositoryGUI
 * @ilCtrl_isCalledBy ilObjAccomodationGUI: ilAdministrationGUI
 * @ilCtrl_isCalledBy ilObjAccomodationGUI: ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjAccomodationGUI: ilPermissionGUI
 * @ilCtrl_Calls ilObjAccomodationGUI: ilInfoScreenGUI
 * @ilCtrl_Calls ilObjAccomodationGUI: ilObjectCopyGUI
 * @ilCtrl_Calls ilObjAccomodationGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjAccomodationGUI: ilObjSettingsGUI
 * @ilCtrl_Calls ilObjAccomodationGUI: ilReservationGUI
 * @ilCtrl_Calls ilObjAccomodationGUI: ilUserReservationsGUI
 * @ilCtrl_Calls ilObjAccomodationGUI: ilExportGUI
 */
class ilObjAccomodationGUI extends ilObjectPluginGUI
{
    const DEFAULT_CMD = 'showContent';
    const TAB_RESERVATION = \ilReservationGUI::CMD_EDIT;
    const TAB_SETTINGS = \ilObjSettingsGUI::CMD_EDIT;
    const TAB_USERS = \ilUserReservationsGUI::CMD_EDIT;
    const TAB_EXPORT = "export";

    /**
     * @var \ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var \ilTabsGUI
     */
    protected $g_tabs;

    /**
     * @var \ilAccess
     */
    protected $g_access;

    /**
     * @var \ilAccomodationPlugin
     */
    protected $plugin;


    /**
     * Called after parent constructor. It's possible to define some plugin special values
     */
    protected function afterConstructor()
    {
        global $DIC;

        $this->g_ctrl = $DIC->ctrl();
        $this->g_tabs = $DIC->tabs();
        $this->g_access = $DIC->access();
        $this->plugin = $this->getPlugin();
        $this->g_user = $DIC->user();
    }

    /**
     * add setting-entry in objSettings
     */
    public function afterSave(\ilObject $newObj)
    {
        parent::afterSave($newObj);
    }

    /**
    * Get type.  Same value as choosen in plugin.php
    */
    final public function getType()
    {
        return "xoac";
    }

    /**
    * After object has been created -> jump to this command
    */
    public function getAfterCreationCmd()
    {
        return self::TAB_SETTINGS;
    }

    /**
    * Get standard command
    */
    public function getStandardCmd()
    {
        return self::TAB_SETTINGS;
    }

    /**
    * Handles all commmands of this class
    */
    public function performCommand($cmd)
    {
        $next_class = $this->g_ctrl->getNextClass();

        switch ($next_class) {
            case 'ilobjsettingsgui':
                $this->forwardGUI(self::TAB_SETTINGS, 'ilObjSettingsGUI');
                break;
            case 'ilreservationgui':
                $this->forwardGUI(self::TAB_RESERVATION, 'ilReservationGUI');
                break;
            case 'iluserreservationsgui':
                $this->forwardGUI(self::TAB_USERS, 'ilUserReservationsGUI');
                break;

            default:
                switch ($cmd) {
                    case self::DEFAULT_CMD:
                    case self::TAB_RESERVATION:
                        $this->redirectGUI(self::TAB_RESERVATION, 'ilReservationGUI');
                        break;

                    case self::TAB_SETTINGS:
                        if (
                            !$this->g_access->checkAccess(
                                "write",
                                "",
                                $this->object->getRefId()
                            )
                        ) {
                            $this->redirectGUI(self::TAB_RESERVATION, 'ilReservationGUI');
                        }
                        $this->redirectGUI($cmd, 'ilObjSettingsGUI');
                        break;

                    case self::TAB_USERS:
                        $this->redirectGUI($cmd, 'ilUserReservationsGUI');
                        break;

                    default:
                        //throw new Exception(__METHOD__.":: Unknown command: " . $cmd);
                        throw new Exception(
                            __METHOD__ . ":: Unknown command: "
                            . $cmd
                            . ' -- '
                            . $next_class
                            );
                }
        }
    }

    /**
     * Check object is below a course object
     *
     * @return bool
     */
    protected function belowCourse()
    {
        return $this->object->getParentCourseInfo() !== false;
    }

    /**
     * Show information accomodation object is not below course
     *
     * @return void
     */
    protected function showNoParentInformation()
    {
        ilUtil::sendInfo($this->plugin->txt("not_below_course"));
    }

    /**
     * forward to another gui and activate tab
     *
     * @param string $tab
     * @param string $gui_class_name
     * @return void
     */
    private function forwardGUI($tab, $gui_class_name)
    {
        $this->g_tabs->activateTab($tab);

        if (!$this->belowCourse()) {
            $this->showNoParentInformation();
            return;
        }

        // use processor for user reservations:
        if ($gui_class_name === 'ilUserReservationsGUI') {
            $backend = new Accomodation\Reservation\UserReservationsBackend($this->object->getActions());
            $table_processor = new TableProcessor($backend);
            $gui = new $gui_class_name(
                $this,
                $this->object->getActions(),
                $this->plugin->txtClosure(),
                $table_processor
            );
        } elseif ($gui_class_name === 'ilReservationGUI') {
            $gui = new $gui_class_name(
                $this->object->getActions(),
                $this->plugin->txtClosure(),
                $this->g_user
            );
        } else {
            $gui = new $gui_class_name(
                $this->object->getActions(),
                $this->plugin->txtClosure()
            );
        }
        $this->g_ctrl->forwardCommand($gui);
    }

    private function redirectGUI($cmd, $gui_class_name)
    {
        if (!$this->belowCourse()) {
            $this->showNoParentInformation();
            return;
        }

        $link = $this->ctrl->getLinkTargetByClass(
            [
                $gui_class_name
            ],
            $cmd,
            "",
            false,
            false

        );

        $this->ctrl->redirectToURL($link);
    }

    /**
    * Set tabs
    */
    protected function setTabs()
    {
        $this->addInfoTab();

        $this->g_tabs->addTab(
            self::TAB_RESERVATION,
            $this->plugin->txt(self::TAB_RESERVATION),
            $this->g_ctrl->getLinkTarget($this, self::TAB_RESERVATION)
        );

        if ($this->g_access->checkAccess("write", "", $this->object->getRefId())) {
            $this->g_tabs->addTab(
                self::TAB_SETTINGS,
                $this->plugin->txt(self::TAB_SETTINGS),
                $this->g_ctrl->getLinkTarget($this, self::TAB_SETTINGS)
            );
        }

        if ($this->g_access->checkAccess("edit_reservations", "", $this->object->getRefId())) {
            $this->g_tabs->addTab(
                self::TAB_USERS,
                $this->plugin->txt(self::TAB_USERS),
                $this->g_ctrl->getLinkTarget($this, self::TAB_USERS)
            );
        }

        $this->addExportTab();

        $this->addPermissionTab();
    }

    /**
    * Goto redirection
    */
    public static function _goto($a_target)
    {
        global $DIC;
        $ctrl = $DIC->ctrl();
        $access = $DIC->access();

        $t = explode("_", $a_target[0]);
        $ref_id = (int) $t[0];
        $class_name = $a_target[1];
        $get = $_GET;

        if ($access->checkAccess("read", "", $ref_id)
            && $access->checkAccess("visible", "", $ref_id)
            && $access->checkAccess("write", "", $ref_id)
            && isset($get["cmd"])
            && ($get["cmd"] == "editProperties")
        ) {
            $ctrl->initBaseClass("ilObjPluginDispatchGUI");
            $ctrl->setTargetScript("ilias.php");
            $ctrl->getCallStructure(strtolower("ilObjPluginDispatchGUI"));
            $ctrl->setParameterByClass($class_name, "ref_id", $ref_id);
            $ctrl->redirectByClass(array("ilobjplugindispatchgui", $class_name, "ilObjSettingsGUI"), $get["cmd"]);
        }

        parent::_goto($a_target);
    }
}

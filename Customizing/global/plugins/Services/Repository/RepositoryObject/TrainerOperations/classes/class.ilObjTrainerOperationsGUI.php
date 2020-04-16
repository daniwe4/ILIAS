<?php

declare(strict_types=1);

use CaT\Plugins\TrainerOperations\ObjTrainerOperations;

require_once __DIR__ . "/Settings/class.ilTrainerOperationsSettingsGUI.php";
require_once __DIR__ . "/Calendar/class.ilTrainerOperationsGUI.php";
require_once __DIR__ . "/UserSettings/class.ilTrainerOperationsCalSettingsGUI.php";
require_once __DIR__ . "/UserSettings/class.ilEditCalendarGUI.php";


/**
 * Plugin object GUI class. Baseclass for all GUI action in ILIAS.
 *
 * @ilCtrl_isCalledBy ilObjTrainerOperationsGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjTrainerOperationsGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 *
 * @ilCtrl_Calls ilObjTrainerOperationsGUI: ilTrainerOperationsSettingsGUI
 * @ilCtrl_Calls ilObjTrainerOperationsGUI: ilTrainerOperationsGUI
 * @ilCtrl_Calls ilObjTrainerOperationsGUI: ilTrainerOperationsCalSettingsGUI
 * @ilCtrl_Calls ilObjTrainerOperationsGUI: ilEditCalendarGUI
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class ilObjTrainerOperationsGUI extends ilObjectPluginGUI
{
    const CMD_EDIT_PROPERTIES = "editProperties";
    const CMD_SHOW_CONTENT = "showContent";

    const TAB_SETTINGS = "settings";
    const TAB_CAL = "calendar";
    const TAB_CAL_SETTINGS = "cal_settings";

    /**
     * @var ilTemplate
     */
    protected $g_tpl;

    /**
     * @var ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var ilTabs
     */
    protected $g_tabs;

    /**
     * @var ilToolbarGUI
     */
    protected $g_toolbar;

    /**
     * @var ilObjUser
     */
    protected $g_usr;

    /**
     * @var ilAccessHandler
     */
    protected $g_access;

    /**
     * @var ilLanguage
     */
    protected $g_lng;

    /**
     * Called after parent constructor. It's possible to define some plugin special values.
     *
     * @return 	void
     */
    protected function afterConstructor()
    {
        global $DIC;

        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->g_ctrl = $DIC->ctrl();
        $this->g_tabs = $DIC->tabs();
        $this->g_toolbar = $DIC->toolbar();
        $this->g_usr = $DIC->user();
        $this->g_access = $DIC->access();
        $this->g_lng = $DIC->language();

        $this->g_lng->loadLanguageModule("");
    }

    /**
    * Get type. Same value as choosen in plugin.php.
    *
    * @return 	void
    */
    final public function getType()
    {
        return ObjTrainerOperations::PLUGIN_ID;
    }

    /**
     * Handles all commmands of this class, centralizes permission checks.
     *
     * @param 	string 	$cmd
     * @return 	void
     */
    public function performCommand($cmd)
    {
        $next_class = $this->g_ctrl->getNextClass();
        $cmd = $this->g_ctrl->getCmd();

        switch ($next_class) {
            case strtolower('ilTrainerOperationsSettingsGUI'):
                $this->forwardSettings();
                break;
            case strtolower('ilTrainerOperationsGUI'):
                $this->forwardCal();
                break;
            case strtolower('ilTrainerOperationsCalSettingsGUI'):
                $this->forwardCalSettings();
                break;
            case strtolower('ilEditCalendarGUI'):
                $this->forwardEditCalendar();
                break;

            default:
                switch ($cmd) {
                    case self::CMD_EDIT_PROPERTIES:
                        $this->redirectSettings();
                        break;

                    case self::CMD_SHOW_CONTENT:
                    case ilTrainerOperationsGUI::CMD_SHOW:
                        $this->redirectCal();
                        break;

                    default:
                        throw new Exception("ilObjTrainerOperationsGUI: Unknown command: " . $cmd);
                }
        }
    }

    /**
     * After object has been created -> jump to this command.
     *
     * @return 	string
     */
    public function getAfterCreationCmd()
    {
        return self::CMD_EDIT_PROPERTIES;
    }

    /**
     * Get standard command.
     *
     * @return 	string
     */
    public function getStandardCmd()
    {
        return ilTrainerOperationsGUI::CMD_SHOW;
    }

    protected function forwardSettings()
    {
        $this->g_tabs->activateTab(self::TAB_SETTINGS);
        $gui = $this->object->getDI()['gui.settings'];
        $this->g_ctrl->forwardCommand($gui);
    }

    protected function forwardCal()
    {
        $this->g_tabs->activateTab(self::TAB_CAL);
        $gui = $this->object->getDI()['gui.calendar'];
        $this->g_ctrl->forwardCommand($gui);
    }
    protected function redirectCal()
    {
        $link = $this->g_ctrl->getLinkTargetByClass(
            array("ilObjTrainerOperationsGUI", "ilTrainerOperationsGUI"),
            ilTrainerOperationsGUI::CMD_SHOW,
            "",
            false,
            false
        );
        \ilUtil::redirect($link);
    }


    protected function forwardCalSettings()
    {
        $this->g_tabs->activateTab(self::TAB_CAL_SETTINGS);
        $gui = $this->object->getDI()['gui.calsettings'];
        $this->g_ctrl->forwardCommand($gui);
    }

    protected function forwardEditCalendar()
    {
        $this->g_tabs->activateTab(self::TAB_CAL_SETTINGS);
        $gui = $this->object->getDI()['gui.editcalendar'];
        $this->g_ctrl->forwardCommand($gui);
    }

    /**
     * Set the title byline to offline if object is offline.
     *
     * @return 	void
     */
    protected function setTitleByline()
    {
        require_once(__DIR__ . "/ilObjTrainerOperationsAccess.php");

        if (ilObjTrainerOperationsAccess::_isOffline()) {
            $this->g_tpl->setAlertProperties(array(
            [
                "alert" => true,
                "property" => $this->g_lng->txt("status"),
                "value" => $this->g_lng->txt("offline")
            ]));
        }
    }


    protected function redirectSettings()
    {
        $link = $this->g_ctrl->getLinkTargetByClass(
            array("ilObjTrainerOperationsGUI", "ilTrainerOperationsSettingsGUI"),
            ilTrainerOperationsSettingsGUI::CMD_EDIT_PROPERTIES,
            "",
            false,
            false
        );
        \ilUtil::redirect($link);
    }

    /**
     * Set the tabs for the site.
     *
     * @return 	void
     */
    protected function setTabs()
    {
        $access_helper = $this->object->getDI()['utils.access'];

        $this->addInfoTab();

        if ($access_helper->mayEditSettings()) {
            $settings = $this->g_ctrl->getLinkTargetByClass(
                array("ilObjTrainerOperationsGUI", "ilTrainerOperationsSettingsGUI"),
                ilTrainerOperationsSettingsGUI::CMD_EDIT_PROPERTIES
            );
            $this->g_tabs->addTab(self::TAB_SETTINGS, $this->txt("settings"), $settings);
        }

        //if ($access_helper->mayEditSettings()) {
        $cal = $this->g_ctrl->getLinkTargetByClass(
                array("ilObjTrainerOperationsGUI", "ilTrainerOperationsGUI"),
                ilTrainerOperationsGUI::CMD_SHOW
            );
        $this->g_tabs->addTab(self::TAB_CAL, $this->txt("calendar"), $cal);
        //}

        if (
            $access_helper->mayEditOwnCalendars()
            || $access_helper->mayEditGeneralCalendars()
        ) {
            $cal_settings = $this->g_ctrl->getLinkTargetByClass(
                array("ilObjTrainerOperationsGUI", "ilTrainerOperationsCalSettingsGUI"),
                ilTrainerOperationsCalSettingsGUI::CMD_SHOW
            );
            $this->g_tabs->addTab(self::TAB_CAL_SETTINGS, $this->txt("calendar_settings"), $cal_settings);
        }

        $this->addPermissionTab();
    }

    public static function _goto($a_target)
    {
        global $DIC;
        $ctrl = $DIC->ctrl();
        $access = $DIC->access();

        $t = explode("_", $a_target[0]);
        $ref_id = (int) $t[0];

        if ($access->checkAccess("write", "", $ref_id)) {
            $class_name = "ilTrainerOperationsSettingsGUI";
            $cmd = ilTrainerOperationsSettingsGUI::CMD_EDIT_PROPERTIES;
        } else {
            $class_name = "ilTrainerOperationsGUI";
            $cmd = ilTrainerOperationsGUI::CMD_SHOW;
        }

        $ctrl->initBaseClass("ilObjPluginDispatchGUI");
        $ctrl->setTargetScript("ilias.php");
        $ctrl->getCallStructure(strtolower("ilObjPluginDispatchGUI"));
        $ctrl->setParameterByClass($class_name, "ref_id", $ref_id);
        $ctrl->redirectByClass(array("ilobjplugindispatchgui", "ilObjTrainerOperationsGUI", $class_name), $cmd);
    }
}

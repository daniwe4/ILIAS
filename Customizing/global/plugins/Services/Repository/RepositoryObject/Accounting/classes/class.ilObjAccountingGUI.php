<?php

declare(strict_types=1);

use CaT\Libs\ExcelWrapper;
use CaT\Plugins\Accounting\DI;

require_once "./Services/Repository/classes/class.ilObjectPluginGUI.php";



/**
 * @ilCtrl_isCalledBy ilObjAccountingGUI: ilRepositoryGUI
 * @ilCtrl_isCalledBy ilObjAccountingGUI: ilAdministrationGUI
 * @ilCtrl_isCalledBy ilObjAccountingGUI: ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjAccountingGUI: ilPermissionGUI
 * @ilCtrl_Calls ilObjAccountingGUI: ilInfoScreenGUI
 * @ilCtrl_Calls ilObjAccountingGUI: ilObjectCopyGUI
 * @ilCtrl_Calls ilObjAccountingGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjAccountingGUI: ilSettingsGUI
 * @ilCtrl_Calls ilObjAccountingGUI: ilDataGUI
 * @ilCtrl_Calls ilObjAccountingGUI: ilFeesGUI
 * @ilCtrl_Calls ilObjAccountingGUI: ilExportGUI
 *
 * Plugin object GUI class. Baseclass for all GUI action in ILIAS
 *
 * @author Daniel Weise 	<daniel.weise@concepts-and-training.de>
 */
class ilObjAccountingGUI extends ilObjectPluginGUI
{
    use DI;

    const TAB_ACCOUNTING = "accounting";
    const TAB_SETTINGS = "editSettings";
    const TAB_PERMISSIONS = "permissions";
    const TAB_FEES = "fees";

    const CMD_SHOW = "editProperties";
    const CMD_SHOW_DATA = "showContent";

    /**
     * @var Pimple\Container
     */
    protected $dic;

    /**
     * Called after parent constructor. It's possible to define some plugin special values
     */
    protected function afterConstructor()
    {
        global $DIC;

        if (!is_null($this->object)) {
            $this->dic = $this->getObjectDIC($this->object, $DIC);
            $this->dic["lng"]->loadLanguageModule("xacc");
        }
    }

    final public function getType()
    {
        return "xacc";
    }

    public function performCommand($cmd)
    {
        $next_class = $this->dic["ilCtrl"]->getNextClass();

        switch ($next_class) {
            case 'ilsettingsgui':
                $this->dic["ilTabs"]->activateTab(self::TAB_SETTINGS);
                $gui = $this->dic["settings.gui"];
                $this->ctrl->forwardCommand($gui);
                break;
            case 'ildatagui':
                $this->dic["ilTabs"]->activateTab(self::TAB_ACCOUNTING);
                $gui = $this->dic["data.gui"];
                $this->ctrl->forwardCommand($gui);
                break;
            case "ilfeesgui":
                $this->dic["ilTabs"]->activateTab(self::TAB_FEES);
                $gui = $this->dic["fees.gui"];
                $this->ctrl->forwardCommand($gui);
                break;
            default:
                switch ($cmd) {
                    case self::CMD_SHOW:
                        if ($this->dic["ilAccess"]->checkAccess("write", "", $this->object->getRefId())) {
                            $this->redirectToSettings();
                        }
                        $this->redirectToData();
                        break;
                    case self::CMD_SHOW_DATA:
                        $this->redirectToData();
                        break;
                    default:
                        throw new Exception("Unknown command: " . $cmd);
                }
        }
    }

    public function getAfterCreationCmd()
    {
        return self::CMD_SHOW;
    }

    public function getStandardCmd()
    {
        return self::CMD_SHOW;
    }

    protected function redirectToSettings()
    {
        ilUtil::redirect($this->dic["settings.gui.link"]);
    }

    protected function redirectToData()
    {
        ilUtil::redirect($this->dic["data.gui.link"]);
    }

    protected function setTabs()
    {
        $accounting = $this->dic["data.gui.link"];
        $settings = $this->dic["settings.gui.link"];
        $fees = $this->dic["fees.fee.gui.link"];

        if ($this->dic["ilAccess"]->checkAccess("read", "", $this->object->getRefId())) {
            $this->dic["ilTabs"]->addTab(self::TAB_ACCOUNTING, $this->txt("accounting"), $accounting);
        }

        if ($this->dic["ilAccess"]->checkAccess("write", "", $this->object->getRefId())) {
            $this->dic["ilTabs"]->addTab(self::TAB_FEES, $this->txt(self::TAB_FEES), $fees);
            $this->dic["ilTabs"]->addTab(self::TAB_SETTINGS, $this->txt("settings"), $settings);
        }

        $this->addExportTab();

        $this->addPermissionTab();
    }
}

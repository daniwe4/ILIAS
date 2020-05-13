<?php
include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/TrainingDemandAdvanced/classes/class.ilTrainingDemandAdvancedSettingsGUI.php';
require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/TrainingDemandAdvanced/classes/class.ilTrainingDemandAdvancedReportGUI.php';

require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';

/**
 * Plugin object GUI class. Baseclass for all GUI action in ILIAS
 *
 * @ilCtrl_isCalledBy ilObjTrainingDemandAdvancedGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjTrainingDemandAdvancedGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
  * @ilCtrl_Calls ilObjTrainingDemandAdvancedGUI: ilTrainingDemandAdvancedSettingsGUI, ilTrainingDemandAdvancedReportGUI
 */
class ilObjTrainingDemandAdvancedGUI extends ilObjectPluginGUI
{
    const TAB_SETTINGS = 'setings';
    const TAB_REPORT = 'report';

    const CMD_SHOW = 'showContent';
    const CMD_TO_SETTINGS = 'editProperties';
    const CMD_TO_REPORT = 'to_report';

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tabs;

    /**
     * @var ilAccess
     */
    protected $access;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilGlobalTemplateInterface
     */
    public $tpl;

    /**
     * Called after parent constructor. It's possible to define some plugin special values
     */
    protected function afterConstructor()
    {
        global $DIC;

        $this->tabs = $DIC->tabs();
        $this->access = $DIC->access();
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->tpl = $DIC->ui()->mainTemplate();
    }

    /**
    * Get type.  Same value as choosen in plugin.php
    */
    final public function getType()
    {
        return "xtda";
    }

    /**
    * Handles all commmands of this class, centralizes permission checks
    */
    public function performCommand()
    {
        $cmd = $this->ctrl->getCmd();
        switch ($this->ctrl->getNextClass()) {
            case "iltrainingdemandadvancedsettingsgui":
                $this->toSettings();
                break;
            case "iltrainingdemandadvancedreportgui":
                $this->toReport();
                break;
            default:
                switch ($cmd) {
                    case self::CMD_SHOW:
                        if ($this->access->checkAccess("write", "", $this->ref_id)) {
                            $this->redirectToSettings();
                        }
                        $this->redirectToReport();
                        break;
                    case self::CMD_TO_SETTINGS:
                        $this->redirectToSettings();
                        break;
                    case self::CMD_TO_REPORT:
                        $this->redirectToReport();
                        break;
                    default:
                        throw new Exception("Unknown command: " . $cmd);
                }
        }
        $this->setTitleAndDescription();
    }

    protected function toReport()
    {
        $this->activateTab(self::TAB_REPORT);
        $gui = new ilTrainingDemandAdvancedReportGUI(
            $this,
            $this->plugin,
            (int) $this->object->getRefId(),
            $this->object->report()
        );
        $this->ctrl->forwardCommand($gui);
    }

    protected function toSettings()
    {
        $this->activateTab(self::TAB_SETTINGS);
        $gui = new ilTrainingDemandAdvancedSettingsGUI(
            $this->ctrl,
            $this->access,
            $this->tpl,
            $this->lng,
            $this->object,
            $this->plugin->txtClosure()
        );
        $this->ctrl->forwardCommand($gui);
    }

    protected function redirectToReport()
    {
        $link = $this->ctrl->getLinkTargetByClass(
            'ilTrainingDemandAdvancedReportGUI',
            ilTrainingDemandAdvancedReportGUI::CMD_VIEW,
            "",
            false,
            false
        );
        $this->ctrl->redirectToURL($link);
    }

    protected function redirectToSettings()
    {
        $link = $this->ctrl->getLinkTargetByClass(
            'ilTrainingDemandAdvancedSettingsGUI',
            ilTrainingDemandAdvancedSettingsGUI::CMD_VIEW,
            "",
            false,
            false
        );
        $this->ctrl->redirectToURL($link);
    }

    public function setTabs()
    {
        $write = $this->access->checkAccess("write", "", $this->object->getRefId());
        $edit_permission = $this->access->checkAccess("edit_permission", "", $this->object->getRefId());
        if ($write || $edit_permission) {
            // standard info screen tab
            $this->addInfoTab();

            // tab for the "show content" command
            if ($this->access->checkAccess("read", "", $this->object->getRefId())) {
                $this->tabs->addTab(
                    self::TAB_REPORT,
                    $this->plugin->txt("content"),
                    $this->ctrl->getLinkTargetByClass('ilTrainingDemandAdvancedReportGUI', ilTrainingDemandAdvancedReportGUI::CMD_VIEW)
                );
            }

            if ($write) {
                // a "properties" tab
                $this->tabs->addTab(
                    self::TAB_SETTINGS,
                    $this->plugin->txt("properties"),
                    $this->ctrl->getLinkTargetByClass('ilTrainingDemandAdvancedSettingsGUI', ilTrainingDemandAdvancedSettingsGUI::CMD_VIEW)
                );
            }

            // standard epermission tab
            $this->addPermissionTab();
        }
    }


    protected function activateTab($cmd)
    {
        $this->tabs->activateTab($cmd);
    }

    /**
    * After object has been created -> jump to this command
    */
    public function getAfterCreationCmd()
    {
        return self::CMD_TO_SETTINGS;
    }

    /**
    * Get standard command
    */
    public function getStandardCmd()
    {
        return self::CMD_TO_REPORT;
    }

    /**
     * @inheritDoc
     */
    public static function _goto($a_target)
    {
        global $DIC;
        $ilCtrl = $DIC->ctrl();
        $t = explode("_", $a_target[0]);
        $ref_id = (int) $t[0];
        $class_name = $a_target[1];

        $ilCtrl->initBaseClass("ilObjPluginDispatchGUI");
        $ilCtrl->getCallStructure(strtolower("ilObjPluginDispatchGUI"));
        $ilCtrl->setParameterByClass($class_name, "ref_id", $ref_id);
        $ilCtrl->redirectByClass(array("ilobjplugindispatchgui", $class_name), ilObjTrainingDemandAdvancedGUI::CMD_SHOW);
    }
}

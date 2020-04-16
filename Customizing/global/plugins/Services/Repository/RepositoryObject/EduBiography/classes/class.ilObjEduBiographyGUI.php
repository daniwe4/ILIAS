<?php

require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/EduBiography/classes/class.ilEduBiographyReportGUI.php';
require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/EduBiography/classes/LinkGeneratorGUI.php';

require_once 'Services/Repository/classes/class.ilObjectPluginGUI.php';
require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';

use CaT\Plugins\EduBiography\DI;
use CaT\Plugins\EduBiography\LinkGeneratorGUI;

/**
 * @ilCtrl_isCalledBy ilObjEduBiographyGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjEduBiographyGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjEduBiographyGUI: ilEduBiographySettingsGUI, ilEduBiographyReportGUI, ilCertificateDownloadGUI
 */
class ilObjEduBiographyGUI extends ilObjectPluginGUI
{
    use DI;

    const TAB_SETTINGS = 'setings';
    const TAB_REPORT = 'report';
    const TAB_CERTIFICATES = 'certificate';

    const CMD_TO_SETTINGS = 'to_settings';
    const CMD_TO_REPORT = 'to_report';

    const GET_TARGET_USR_ID = 'target_user_id';

    /**
     * @var \Pimple\Container
     */
    protected $dic;

    /**
     * @var ilObjEduBiography
     */
    public $object;

    /**
     * @var
     */
    protected $link_generator_gui;

    /**
     * Called after parent constructor. It's possible to define some plugin special values
     */


    /**
    * Get type.  Same value as choosen in plugin.php
    */
    final public function getType()
    {
        return "xebr";
    }

    /**
    * Handles all commmands of this class, centralizes permission checks
    */
    public function performCommand()
    {
        $cmd = $this->getDic()["ilCtrl"]->getCmd();
        switch ($this->getDic()["ilCtrl"]->getNextClass()) {
            case "iledubiographysettingsgui":
                $this->toSettings();
                break;
            case "iledubiographyreportgui":
                $this->toReport();
                break;
            case "ilcertificatedownloadgui":
                $this->toCertificates();
                break;
            default:
                switch ($cmd) {
                    case self::CMD_TO_SETTINGS:
                        $this->redirectToSettings();
                        break;
                    case self::CMD_TO_REPORT:
                        $this->redirectToReport();
                        break;
                    default:
                        $this->redirectToReport();
                }
        }
        $this->setTitleAndDescription();
    }

    protected function toSettings()
    {
        $this->activateTab(self::TAB_SETTINGS);
        $gui = $this->getDIC()["settings.gui"];
        $this->getDic()["ilCtrl"]->forwardCommand($gui);
    }

    protected function toReport()
    {
        $usr_locator = $this->object->userOrguLocator();
        $this->activateTab(self::TAB_REPORT);

        if ($this->shouldShowOverview()) {
            $gui = new ilEduBiographyReportGUI(
                $this->plugin,
                (int) $this->object->getRefId(),
                $this->object->userOrguLocator(),
                $this->object->overviewReport($this->getLinkGenerator())
            );
            $this->getLinkGenerator()->setReportGui($gui);
        } else {
            $target_usr_id = (int) $_GET[self::GET_TARGET_USR_ID];
            if (!$target_usr_id) {
                $target_usr_id = (int) $this->getDic()["ilUser"]->getId();
            } elseif (!$usr_locator->isUserIdVisibleToUser((int) $target_usr_id, $this->getDic()["ilUser"])) {
                $target_usr_id = (int) $this->getDic()["ilUser"]->getId();
                \ilUtil::sendFailure($this->plugin->txt('may_not_view_user'), true);
                $this->getDic()["ilCtrl"]->redirect($this);
            }

            $file_storage = $this->getDIC()["filestorage"];
            $user_file_storage = $file_storage->withUserId($target_usr_id);

            $gui = new ilEduBiographyReportGUI(
                $this->plugin,
                (int) $this->object->getRefId(),
                $this->object->userOrguLocator(),
                $this->object->detailReport($target_usr_id, $user_file_storage),
                $file_storage->withUserId($target_usr_id),
                $this->object->detailReportSummary($target_usr_id)
            );
            $gui->addRelevantParameter(self::GET_TARGET_USR_ID, $target_usr_id);
            if ($this->object->settings()->hasSuperiorOverview()) {
                $name_params = \ilObjUser::_lookupName($target_usr_id);
                $this->object->setTitle(sprintf($this->plugin->txt('view_edubio_of'), $name_params['firstname'], $name_params['lastname']));
            }
        }
        $this->getDic()["ilCtrl"]->forwardCommand($gui);
    }

    protected function toCertificates()
    {
        $this->activateTab(self::TAB_CERTIFICATES);
        $gui = $this->getDIC()["certificate.gui"];
        $this->getDic()["ilCtrl"]->forwardCommand($gui);
    }

    protected function shouldShowOverview()
    {
        return $this->object->settings()->hasSuperiorOverview() && trim((string) $_GET[self::GET_TARGET_USR_ID]) === '';
    }

    protected function redirectToSettings()
    {
        $link = $this->getDIC()["settings.gui.link"];
        $this->getDIC()["ilCtrl"]->redirectToURL($link);
    }

    protected function redirectToReport()
    {
        $link = $this->ctrl->getLinkTargetByClass('ilEduBiographyReportGUI', ilEduBiographyReportGUI::CMD_VIEW, "", false, false);
        $this->getDIC()["ilCtrl"]->redirectToURL($link);
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

    public function setTabs()
    {
        $write = $this->getDic()["ilAccess"]->checkAccess("write", "", $this->object->getRefId());
        $edit_permission = $this->getDic()["ilAccess"]->checkAccess("edit_permission", "", $this->object->getRefId());
        $read = $this->getDic()["ilAccess"]->checkAccess("read", "", $this->object->getRefId());
        $certificates = $this->getDic()["config.activation.db"]->select()->isActive() &&
            $this->getDic()["ilAccess"]->checkAccess("download_overview_certificate", "", $this->object->getRefId());

        if ($write || $edit_permission) {
            $this->addInfoTab();
        }

        if ($read) {
            $this->getDic()["ilTabs"]->addTab(
                self::TAB_REPORT,
                $this->plugin->txt("content"),
                $this->getDic()["ilCtrl"]->getLinkTargetByClass('ilEduBiographyReportGUI', ilEduBiographyReportGUI::CMD_VIEW)
            );

            if ($certificates) {
                $this->getDic()["ilTabs"]->addTab(
                    self::TAB_CERTIFICATES,
                    $this->plugin->txt("certificates"),
                    $this->getDic()["certificate.gui.link"]
                );
            }
        }

        if ($write) {
            $this->getDic()["ilTabs"]->addTab(
                self::TAB_SETTINGS,
                $this->plugin->txt("properties"),
                $this->getDic()["settings.gui.link"]
            );
        }

        if ($edit_permission) {
            $this->addPermissionTab();
        }
    }

    protected function activateTab($cmd)
    {
        $this->getDic()["ilTabs"]->activateTab($cmd);
    }

    protected function getDIC() : \Pimple\Container
    {
        if (is_null($this->dic)) {
            global $DIC;
            $this->dic = $this->getObjectDIC($this->object, $DIC);
        }
        
        return $this->dic;
    }

    protected function getLinkGenerator()
    {
        if (is_null($this->link_generator_gui)) {
            $this->link_generator_gui = new LinkGeneratorGUI($this->getDIC()["ilCtrl"]);
        }
        return $this->link_generator_gui;
    }

    public static function _goto($a_target)
    {
        $ref_id = (int) $a_target[0];

        global $DIC;
        $ctrl = $DIC["ilCtrl"];
        $ctrl->setTargetScript("ilias.php");
        $ctrl->initBaseClass("ilobjplugindispatchgui");
        $ctrl->getCallStructure(strtolower("ilObjPluginDispatchGUI"));

        $ctrl->setParameterByClass("ilEduBiographyReportGUI", "ref_id", $ref_id);
        $link = $ctrl->getLinkTargetByClass(
            ["ilObjPluginDispatchGUI","ilObjEduBiographyGUI", 'ilEduBiographyReportGUI'],
            ilEduBiographyReportGUI::CMD_VIEW,
            "",
            false,
            false
        );

        $ctrl->clearParametersByClass("ilEduBiographyReportGUI");
        $ctrl->redirectToURL($link);
    }
}

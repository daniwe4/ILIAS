<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Plugins\Agenda\TableProcessing;
use CaT\Plugins\Agenda\AgendaEntry\AgendaEntryBackend;
use CaT\Plugins\Agenda\EntryChecks\EntryChecks;
use CaT\Plugins\Agenda\DI;

require_once 'Services/Repository/classes/class.ilObjectPluginGUI.php';
require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';

require_once __DIR__ . "/Settings/class.ilAgendaSettingsGUI.php";
require_once __DIR__ . "/AgendaEntry/class.ilAgendaEntriesGUI.php";

/**
 * @ilCtrl_isCalledBy ilObjAgendaGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjAgendaGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjAgendaGUI: ilAgendaSettingsGUI, ilAgendaEntriesGUI, ilExportGUI
 */
class ilObjAgendaGUI extends ilObjectPluginGUI
{
    use DI;

    const CMD_SHOW_CONTENT = "showContent";
    const TAB_SETTINGS = "tab_settings";
    const TAB_ENTRIES = "tab_entries";

    /**
     * @var DI
     */
    protected $dic;

    protected function afterConstructor()
    {
        global $DIC;
        if (!is_null($this->object)) {
            $this->dic = $this->getObjectDIC($this->object, $DIC);
        }
    }

    final public function getType()
    {
        return "xage";
    }

    public function performCommand(string $cmd)
    {
        $next_class = $this->dic["ilCtrl"]->getNextClass();
        switch ($next_class) {
            case "ilagendasettingsgui":
                $this->forwardSettings();
                break;
            case "ilagendaentriesgui":
                $this->forwardAgendaEntries();
                break;
            default:
                switch ($cmd) {
                    case ilAgendaSettingsGUI::CMD_EDIT_PROPERTIES:
                        if ($this->dic["ilAccess"]->checkAccess("write", "", $this->object->getRefId())) {
                            $this->redirectToSettings();
                        } else {
                            $this->redirectInfoTab();
                        }
                        break;

                    default:
                        throw new Exception("Unknown command: " . $cmd);
                }
        }
    }

    protected function forwardSettings()
    {
        $this->activateTab(self::TAB_SETTINGS);
        $this->dic["ilCtrl"]->forwardCommand($this->dic["settings.gui"]);
    }

    protected function forwardAgendaEntries()
    {
        $this->activateTab(self::TAB_ENTRIES);
        $this->dic["ilCtrl"]->forwardCommand($this->dic["agenda_entry.gui"]);
    }

    /**
    * After object has been created -> jump to this command
    */
    public function getAfterCreationCmd()
    {
        return ilAgendaSettingsGUI::CMD_EDIT_PROPERTIES;
    }

    /**
    * Get standard command
    */
    public function getStandardCmd()
    {
        return ilAgendaSettingsGUI::CMD_EDIT_PROPERTIES;
    }

    /**
     * Redirect to settings gui to keep next_class options
     *
     * @return void
     */
    protected function redirectToSettings()
    {
        $link = $this->dic["settings.gui.link"];
        \ilUtil::redirect($link);
    }

    /**
     * Redirect via link to Info tab
     *
     * @return null
     */
    protected function redirectInfoTab()
    {
        $link = $this->dic["ilCtrl"]->getLinkTargetByClass(
            array("ilInfoScreenGUI"),
            "showSummary",
            "",
            false,
            false
        );
        \ilUtil::redirect($link);
    }

    public function setTabs()
    {
        $this->addInfoTab();

        if ($this->dic["ilAccess"]->checkAccess("write", "", $this->object->getRefId())) {
            $link = $this->dic["settings.gui.link"];
            $this->dic["ilTabs"]->addTab(self::TAB_SETTINGS, $this->txt(self::TAB_SETTINGS), $link);
        }

        if ($this->dic["ilAccess"]->checkAccess("view_agenda_entries", "", $this->object->getRefId())) {
            $link = $this->dic["agenda.entry.gui.link"];
            $this->dic["ilTabs"]->addTab(self::TAB_ENTRIES, $this->txt(self::TAB_ENTRIES), $link);
        }

        $this->addExportTab();

        $this->addPermissionTab();
    }

    protected function activateTab(string $cmd)
    {
        $this->dic["ilTabs"]->activateTab($cmd);
    }
}

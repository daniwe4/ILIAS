<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\Agenda;

use Pimple\Container;
use CaT\Plugins\Agenda\AgendaEntry;
use CaT\Plugins\Agenda\TableProcessing;
use CaT\Plugins\Agenda\EntryChecks;

trait DI
{
    public function getPluginDIC(
        \ilAgendaPlugin $plugin,
        \ArrayAccess $dic
    ) : Container {
        $container = new Container();

        $container["ilDB"] = function ($c) use ($dic) {
            return $dic["ilDB"];
        };
        $container["ilCtrl"] = function ($c) use ($dic) {
            return $dic["ilCtrl"];
        };
        $container["ilUser"] = function ($c) use ($dic) {
            return $dic["ilUser"];
        };
        $container["ilTabs"] = function ($c) use ($dic) {
            return $dic["ilTabs"];
        };
        $container["tpl"] = function ($c) use ($dic) {
            return $dic["tpl"];
        };
        $container["txtclosure"] = function ($c) use ($plugin) {
            return function ($code) use ($plugin) {
                return $plugin->txt($code);
            };
        };

        $container["plugin.path"] = function ($c) use ($plugin) {
            return $plugin->getDirectory();
        };

        $container["config.blocks.db"] = function ($c) use ($dic) {
            return new Config\Blocks\ilDB(
                $c["ilDB"],
                $c["ilUser"]
            );
        };

        $container["config.blocks.gui"] = function ($c) use ($dic) {
            require_once __DIR__ . "/Config/Blocks/class.ilBlocksGUI.php";
            return new \ilBlocksGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["config.blocks.db"],
                $c["txtclosure"]
            );
        };

        $container["config.blocks.gui.link"] = function ($c) use ($dic) {
            require_once __DIR__ . "/Config/Blocks/class.ilBlocksGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilBlocksGUI",
                \ilBlocksGUI::CMD_SHOW_CONFIG,
                "",
                false,
                false
            );
        };

        return $container;
    }

    public function getObjectDIC(
        \ilObjAgenda $object,
        \ArrayAccess $dic
    ) : Container {
        $container = new Container();

        $container["ilDB"] = function ($c) use ($dic) {
            return $dic["ilDB"];
        };

        $container["ilCtrl"] = function ($c) use ($dic) {
            return $dic["ilCtrl"];
        };

        $container["tree"] = function ($c) use ($dic) {
            return $dic["tree"];
        };

        $container["tree_discovery"] = function ($c) {
            return new \ilTreeObjectDiscovery($c["tree"]);
        };

        $container["ilTabs"] = function ($c) use ($dic) {
            return $dic["ilTabs"];
        };

        $container["tpl"] = function ($c) use ($dic) {
            return $dic["tpl"];
        };

        $container["ilUser"] = function ($c) use ($dic) {
            return $dic["ilUser"];
        };

        $container["ilAppEventHandler"] = function ($c) use ($dic) {
            return $dic["ilAppEventHandler"];
        };

        $container["objDefinition"] = function ($c) use ($dic) {
            return $dic["objDefinition"];
        };

        $container["ilAccess"] = function ($c) use ($dic) {
            return $dic["ilAccess"];
        };

        $container["ilToolbar"] = function ($c) use ($dic) {
            return $dic["ilToolbar"];
        };

        $container["plugin.path"] = function ($c) use ($object) {
            return $object->getDirectory();
        };

        $container["object.id"] = function ($c) use ($object) {
            return (int) $object->getId();
        };

        $container["object.ref_id"] = function ($c) use ($object) {
            return (int) $object->getRefId();
        };

        $container["txtclosure"] = function ($c) use ($object) {
            return function ($code) use ($object) {
                return $object->pluginTxt($code);
            };
        };

        $container["entry_checks.entry_checks"] = function ($c) {
            return new EntryChecks\EntryChecks();
        };

        $container["agenda_entry.db"] = function ($c) {
            return new AgendaEntry\ilDB($c["ilDB"]);
        };

        $container["agenda_entry.backend"] = function ($c) {
            return new AgendaEntry\AgendaEntryBackend($c["agenda_entry.db"]);
        };

        $container["agenda_entry.table_processor"] = function ($c) {
            return new TableProcessing\TableProcessor($c["agenda_entry.backend"]);
        };

        $container["config.blocks.db"] = function ($c) use ($dic) {
            return new Config\Blocks\ilDB(
                $c["ilDB"],
                $c["ilUser"]
            );
        };

        $container["aip.agenda.item.db"] = function ($c) {
            if (\ilPluginAdmin::isPluginActive("xaip")) {
                $aip = \ilPluginAdmin::getPluginObjectById("xaip");
                return $aip->getAgendaItemDB($c["ilDB"]);
            }
            return null;
        };

        $container["agenda_entry.gui"] = function ($c) use ($object) {
            require_once __DIR__ . "/AgendaEntry/class.ilAgendaEntriesGUI.php";
            return new \ilAgendaEntriesGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["ilToolbar"],
                $c["ilAccess"],
                $c["agenda_entry.db"],
                $c["agenda_entry.table_processor"],
                $c["entry_checks.entry_checks"],
                $object,
                $c["plugin.path"],
                $c["txtclosure"],
                $c["config.blocks.db"],
                $c["tree_discovery"]
            );
        };

        $container["agenda.entry.gui.link"] = function ($c) {
            require_once __DIR__ . "/AgendaEntry/class.ilAgendaEntriesGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                [
                    "ilObjAgendaGUI",
                    "ilAgendaEntriesGUI"
                ],
                \ilAgendaEntriesGUI::CMD_SHOW_ENTRIES,
                "",
                false,
                false
            );
        };

        $container["course_creation.db"] = function ($c) {
            return new CourseCreation\ilDB($c["ilDB"]);
        };

        $container["settings.gui"] = function ($c) use ($object) {
            require_once __DIR__ . "/Settings/class.ilAgendaSettingsGUI.php";
            return new \ilAgendaSettingsGUI(
                $object,
                $c["tpl"],
                $c["ilCtrl"]
            );
        };

        $container["settings.db"] = function ($c) {
            return new Settings\ilDB($c["ilDB"]);
        };

        $container["settings.gui.link"] = function ($c) {
            require_once __DIR__ . "/Settings/class.ilAgendaSettingsGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                [
                    "ilObjAgendaGUI",
                    "ilAgendaSettingsGUI"
                ],
                \ilAgendaSettingsGUI::CMD_EDIT_PROPERTIES,
                "",
                false,
                false
            );
        };

        return $container;
    }
}

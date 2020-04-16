<?php

/* Copyright (c) 2018 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WBDManagement;

use CaT\Plugins\WBDManagement\Settings\ilDB;
use CaT\Plugins\WBDManagement\Settings\FileStorage;
use CaT\Plugins\WBDManagement\Reports;
use ILIAS\TMS\TableRelations;
use ILIAS\TMS\Filter;
use Pimple\Container;
use CaT\WBD\ErrorLog;

trait DI
{
    public function getPluginDIC(\ilWBDManagementPlugin $plugin, \ArrayAccess $dic) : Container
    {
        $container = new Container();

        $container["lng"] = function ($c) use ($dic) {
            return $dic["lng"];
        };

        $container["ilDB"] = function ($c) use ($dic) {
            return $dic["ilDB"];
        };

        $container["ilTabs"] = function ($c) use ($dic) {
            return $dic["ilTabs"];
        };

        $container["ilCtrl"] = function ($c) use ($dic) {
            return $dic["ilCtrl"];
        };

        $container["tpl"] = function ($c) use ($dic) {
            return $dic["tpl"];
        };

        $container["ilToolbar"] = function ($c) use ($dic) {
            return $dic["ilToolbar"];
        };

        $container["ilUser"] = function ($c) use ($dic) {
            return $dic["ilUser"];
        };

        $container["ilSetting"] = function ($c) use ($dic) {
            return $dic["ilSetting"];
        };

        $container["rbacreview"] = function ($c) use ($dic) {
            return $dic["rbacreview"];
        };

        $container["rbacadmin"] = function ($c) use ($dic) {
            return $dic["rbacadmin"];
        };

        $container["plugin.path"] = function ($c) use ($plugin) {
            return $plugin->getDirectory();
        };

        $container["txtclosure"] = function ($c) use ($plugin) {
            return function ($code) use ($plugin) {
                return $plugin->txt($code);
            };
        };

        $container["udf.field.gut_beraten_id"] = function ($c) {
            return "gut_beraten_id";
        };

        $container["udf.field.gut_beraten_status"] = function ($c) {
            return "gut_beraten_status";
        };

        $container["udf.fields"] = function ($c) {
            return [
                $c["udf.field.gut_beraten_id"],
                $c["udf.field.gut_beraten_status"]
            ];
        };

        $container["config.user_defined_fields.storage"] = function ($c) {
            return new Config\UserDefinedFields\ilWBDManagementUDFStorage(
                $c["ilSetting"]
            );
        };

        $container["config.user_defined_fields.gui"] = function ($c) {
            require_once __DIR__ . "/Config/UserDefinedFields/class.ilWBDManagementUDFConfigGUI.php";
            return new \ilWBDManagementUDFConfigGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["ilToolbar"],
                $c["txtclosure"],
                $c["config.user_defined_fields.storage"],
                $c["plugin.path"],
                $c["udf.fields"]
            );
        };

        $container["config.user_defined_fields.link"] = function ($c) {
            require_once __DIR__ . "/Config/UserDefinedFields/class.ilWBDManagementUDFConfigGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilWBDManagementUDFConfigGUI",
                \ilWBDManagementUDFConfigGUI::CMD_SHOW,
                "",
                false,
                false
            );
        };

        $container["gut.beraten.db"] = function ($c) {
            return new GutBeraten\ilDB(
                $c["ilDB"]
            );
        };

        $container["udf.wbd.id.key"] = function ($c) {
            return $c["config.user_defined_fields.storage"]
                ->read($c["udf.field.gut_beraten_id"])
                    ->getUdfId()
            ;
        };

        $container["udf.wbd.status.key"] = function ($c) {
            return $c["config.user_defined_fields.storage"]
                ->read($c["udf.field.gut_beraten_status"])
                ->getUdfId()
                ;
        };

        return $container;
    }

    public function getObjectDIC(\ilObjWBDManagement $object, \ArrayAccess $dic) : Container
    {
        $container = new Container();

        $container["lng"] = function ($c) use ($dic) {
            return $dic["lng"];
        };

        $container["ilDB"] = function ($c) use ($dic) {
            return $dic["ilDB"];
        };

        $container["ilTabs"] = function ($c) use ($dic) {
            return $dic["ilTabs"];
        };

        $container["ilCtrl"] = function ($c) use ($dic) {
            return $dic["ilCtrl"];
        };

        $container["tpl"] = function ($c) use ($dic) {
            return $dic["tpl"];
        };

        $container["ilToolbar"] = function ($c) use ($dic) {
            return $dic["ilToolbar"];
        };

        $container["ilUser"] = function ($c) use ($dic) {
            return $dic["ilUser"];
        };

        $container["ilSetting"] = function ($c) use ($dic) {
            return $dic["ilSetting"];
        };

        $container["rbacreview"] = function ($c) use ($dic) {
            return $dic["rbacreview"];
        };

        $container["rbacadmin"] = function ($c) use ($dic) {
            return $dic["rbacadmin"];
        };

        $container["ilAccess"] = function ($c) use ($dic) {
            return $dic["ilAccess"];
        };

        $container["ui.factory"] = function ($c) use ($dic) {
            return $dic["ui.factory"];
        };

        $container["ui.renderer"] = function ($c) use ($dic) {
            return $dic["ui.renderer"];
        };

        $container["txtclosure"] = function ($c) use ($object) {
            return $object->txtClosure();
        };

        $container["object"] = function ($c) use ($object) {
            return $object;
        };

        $container["object.ref_id"] = function ($c) use ($object) {
            return (int) $object->getRefId();
        };

        $container["plugin.dir"] = function ($c) use ($object) {
            return $object->getPluginDir();
        };

        $container["udf.field.gut_beraten_id"] = function ($c) {
            return "gut_beraten_id";
        };

        $container["udf.field.gut_beraten_status"] = function ($c) {
            return "gut_beraten_status";
        };

        $container["config.user_defined_fields.storage"] = function ($c) {
            return new Config\UserDefinedFields\ilWBDManagementUDFStorage(
                $c["ilSetting"]
            );
        };

        $container["plugin.dir"] = function ($c) use ($object) {
            return $object->getPluginDirectory();
        };

        $container["gut.beraten.gui"] = function ($c) use ($object) {
            require_once __DIR__ . "/GutBeraten/class.ilGutBeratenGUI.php";
            return new \ilGutBeratenGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["ilUser"],
                $c["ilAccess"],
                $c["ilToolbar"],
                $c["ui.factory"],
                $c["ui.renderer"],
                $object->getSettings(),
                $c["gut.beraten.db"],
                $c["txtclosure"],
                $c["object.ref_id"],
                $c["gut.beraten.gui.link"],
                $c["plugin.dir"]
            );
        };

        $container["gut.beraten.db"] = function ($c) {
            return new GutBeraten\ilDB(
                $c["ilDB"]
            );
        };

        $container["gut.beraten.gui.link"] = function ($c) {
            require_once __DIR__ . "/GutBeraten/class.ilGutBeratenGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                [
                    "ilObjWBDManagementGUI",
                    "ilGutBeratenGUI"
                ],
                \ilGutBeratenGUI::CMD_SHOW,
                "",
                false,
                false
            );
        };

        $container["settings.db"] = function ($c) {
            require_once __DIR__ . "/Settings/ilDB.php";
            return new ilDB($c["ilDB"]);
        };

        $container["settings.gui"] = function ($c) {
            require_once __DIR__ . "/Settings/class.ilWBDManagementSettingsGUI.php";
            return new \ilWBDManagementSettingsGUI(
                $c["object"],
                $c["ilCtrl"],
                $c["tpl"],
                $c["txtclosure"],
                $c["settings.file.storage"],
                $c["ui.factory"],
                $c["ui.renderer"],
                $c["settings.gui.link"]
            );
        };

        $container["settings.gui.link"] = function ($c) {
            require_once __DIR__ . "/Settings/class.ilWBDManagementSettingsGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilWBDManagementSettingsGUI",
                \ilWBDManagementSettingsGUI::CMD_SHOW,
                "",
                false,
                false
            );
        };

        $container["settings.file.storage"] = function ($c) {
            require_once __DIR__ . "/Settings/FileStorage.php";
            return new FileStorage($c["object.ref_id"]);
        };





        $container["reports.gui.link"] = function ($c) {
            require_once __DIR__ . "/Reports/ErrorReport/class.ilWBDReportGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilWBDReportGUI",
                \ilWBDReportGUI::CMD_VIEW,
                "",
                false,
                false
            );
        };

        $container["reports.gui"] = function ($c) {
            require_once __DIR__ . "/Reports/ErrorReport/class.ilWBDReportGUI.php";
            return new \ilWBDReportGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["reports.report"],
                $c["manager.errorlog.db"],
                $c["txtclosure"]
            );
        };

        $container["reports.report"] = function ($c) {
            return new Reports\ErrorReport\Report(
                $c["txtclosure"],
                $c["plugin.dir"],
                $c["tms.table.factory"],
                $c["tms.filter.filterfactory"],
                $c["tms.filter.typefactory"],
                $c["tms.filter.predicatefactory"],
                $c["tms.table.interpreter"],
                $c["helper.actionlinks"],
                $c["helper.crslinks"],
                $c["ilDB"]
            );
        };

        $container["reports.not_yet_reported.link"] = function ($c) {
            require_once __DIR__ . "/Reports/NotYetReported/class.ilNotYetReportedGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilNotYetReportedGUI",
                \ilNotYetReportedGUI::CMD_SHOW_TABLE,
                "",
                false,
                false
            );
        };

        $container["reports.not_yet_reported.gui"] = function ($c) {
            require_once __DIR__ . "/Reports/NotYetReported/class.ilNotYetReportedGUI.php";
            return new \ilNotYetReportedGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["cases.db"],
                $c["config.user_defined_fields.storage"],
                $c["txtclosure"],
                $c["plugin.dir"],
                $c["udf.field.gut_beraten_id"],
                $c["udf.field.gut_beraten_status"],
                $c["reporting_start_date"]
            );
        };

        $container["reporting_start_date"] = function ($c) {
            $start_date = null;
            if (\ilPluginAdmin::isPluginActive("WBDCommunicator")) {
                $pl = \ilPluginAdmin::getPluginObjectById("WBDCommunicator");
                $start_date = $pl->getAnnouncementStartDate();
            }
            return $start_date;
        };

        $container["reports.not_yet_cancelled.link"] = function ($c) {
            require_once __DIR__ . "/Reports/NotYetCancelled/class.ilNotYetCancelledGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilNotYetCancelledGUI",
                \ilNotYetCancelledGUI::CMD_SHOW_TABLE,
                "",
                false,
                false
            );
        };

        $container["reports.not_yet_cancelled.gui"] = function ($c) {
            require_once __DIR__ . "/Reports/NotYetCancelled/class.ilNotYetCancelledGUI.php";
            return new \ilNotYetCancelledGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["cases.db"],
                $c["config.user_defined_fields.storage"],
                $c["txtclosure"],
                $c["plugin.dir"]
            );
        };

        $container["manager.errorlog.db"] = function ($c) {
            return new Reports\ErrorReport\ilDB(
                $c["ilDB"]
            );
        };

        $container["wbd.errorlog.db"] = function ($c) {
            return new ErrorLog\ilDB(
                $c["ilDB"]
            );
        };

        $container["info.gui.link"] = function ($c) {
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilInfoScreenGUI",
                "showSummary",
                "",
                false,
                false
            );
        };

        $container["helper.actionlinks"] = function ($c) {
            return new Reports\ErrorReport\ActionLinksHelper(
                "ilWBDReportGUI",
                $c["ilCtrl"]
            );
        };

        $container["helper.crslinks"] = function ($c) {
            return new Reports\ErrorReport\CrsLinkHelper(
                $c["ui.renderer"],
                $c["ui.factory"]
            );
        };

        $container["tms.table.factory"] = function ($c) {
            return new TableRelations\TableFactory(
                $c["tms.filter.predicatefactory"],
                $c["tms.table.graphfactory"]
            );
        };

        $container["tms.table.interpreter"] = function ($c) {
            return new TableRelations\SqlQueryInterpreter(
                $c["tms.filter.sqlpredicateinterpreter"],
                $c["tms.filter.predicatefactory"],
                $c["ilDB"]
            );
            ;
        };

        $container["tms.filter.filterfactory"] = function ($c) {
            return new Filter\FilterFactory(
                $c["tms.filter.predicatefactory"],
                $c["tms.filter.typefactory"]
            );
        };

        $container["tms.table.graphfactory"] = function ($c) {
            return new TableRelations\GraphFactory();
        };

        $container["tms.filter.sqlpredicateinterpreter"] = function ($c) {
            return new Filter\SqlPredicateInterpreter(
                $c["ilDB"]
            );
        };

        $container["tms.filter.typefactory"] = function ($c) {
            return new Filter\TypeFactory();
        };

        $container["tms.filter.predicatefactory"] = function ($c) {
            return new Filter\PredicateFactory();
        };

        $container["config.contact.db"] = function ($c) {
            require_once "Services/TMS/WBD/Contact/ilContactDB.php";
            return new \ilContactDB();
        };

        $container["cases.db"] = function ($c) {
            require_once "Services/TMS/WBD/Cases/ilCasesDB.php";
            return new \ilCasesDB(
                $c["ilDB"],
                $c["tms.table.interpreter"],
                $c["tms.table.factory"],
                $c["tms.filter.predicatefactory"],
                $c["tms.filter.typefactory"],
                $c["wbd.errorlog.db"],
                $c["config.contact.db"]
            );
        };

        return $container;
    }
}

<?php

declare(strict_types=1);

namespace CaT\Plugins\EmployeeBookingOverview;

use Pimple\Container;
use ILIAS\TMS\Filter;
use ILIAS\TMS\TableRelations;

trait DI
{
    public function getObjectDIC(
        \ilObjEmployeeBookingOverview $object,
        \ArrayAccess $dic
    ) : Container {
        $container = new Container();

        $container["ilCtrl"] = function ($c) use ($dic) {
            return $dic["ilCtrl"];
        };
        $container["ilTabs"] = function ($c) use ($dic) {
            return $dic["ilTabs"];
        };
        $container["tpl"] = function ($c) use ($dic) {
            return $dic["tpl"];
        };
        $container["ilDB"] = function ($c) use ($dic) {
            return $dic["ilDB"];
        };
        $container["ilAccess"] = function ($c) use ($dic) {
            return $dic["ilAccess"];
        };
        $container["lng"] = function ($c) use ($dic) {
            return $dic["lng"];
        };
        $container["ilUser"] = function ($c) use ($dic) {
            return $dic["ilUser"];
        };
        $container["tree"] = function ($c) use ($dic) {
            return $dic["tree"];
        };

        $container["txtclosure"] = function ($c) use ($object) {
            return $object->txtClosure();
        };
        $container["plugindir"] = function ($c) use ($object) {
            return $object->getPluginDir();
        };

        $container["settings.gui.db"] = function ($c) {
            return new Settings\DBSettingsRepository(
                $c["ilDB"]
            );
        };
        $container["settings.gui.link"] = function ($c) {
            require_once __DIR__ . "/Settings/class.ilEmployeeBookingOverviewSettingsGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilEmployeeBookingOverviewSettingsGUI",
                \ilEmployeeBookingOverviewSettingsGUI::CMD_VIEW,
                "",
                false,
                false
            );
        };
        $container["settings.gui"] = function ($c) use ($object) {
            require_once __DIR__ . "/Settings/class.ilEmployeeBookingOverviewSettingsGUI.php";
            return new \ilEmployeeBookingOverviewSettingsGUI(
                $object,
                $c["ilCtrl"],
                $c["tpl"],
                $c["access.checker"],
                $c["txtclosure"],
                $c["settings.gui.db"]
            );
        };

        $container["report.base.gui"] = function ($c) use ($object) {
            return new \ilEmployeeBookingOverviewReportGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["filter.factory.view"],
                $c["factory.export"],
                $c["factory.report.tables"],
                $c["access.checker"],
                $c["filter.display"],
                $c["report"],
                $c['report.base.userAutocomplete'],
                (int) $object->getRefId()
            );
        };

        $container['report.base.userAutocomplete'] = function ($c) {
            return new \ilEmployeeBookingOverviewUserAutoComplete($c['ilDB']);
        };

        $container["report"] = function ($c) use ($object) {
            return new Report(
                $object,
                $c["ilDB"],
                $c["ilUser"],
                $c["txtclosure"],
                $c["tree.discovery"],
                $c["orgu.user.locator"],
                $c["filter.factory.predicate"],
                $c["table.factory"],
                $c["filter.factory.type"],
                $c["filter.factory"],
                $c["plugindir"]
            );
        };
        $container["orgu.user.locator"] = function ($c) {
            return new UserOrguLocator(
                $c["orgu.tree"],
                $c["ilAccess"],
                $c["orgu.tms.poshelper"]
            );
        };
        $container["orgu.tree"] = function ($c) {
            return \ilObjOrgUnitTree::_getInstance();
        };
        $container["orgu.tms.poshelper"] = function ($c) {
            return new \TMSPositionHelper(\ilOrgUnitUserAssignmentQueries::getInstance());
        };
        $container["tree.discovery"] = function ($c) {
            return new \ilTreeObjectDiscovery(
                $c["tree"]
            );
        };

        $container["table.factory.graph"] = function ($c) {
            return new TableRelations\GraphFactory();
        };
        $container["table.factory"] = function ($c) {
            return new TableRelations\TableFactory(
                $c["filter.factory.predicate"],
                $c["table.factory.graph"]
            );
        };
        $container["filter.factory.predicate"] = function ($c) {
            return new Filter\PredicateFactory();
        };
        $container["filter.factory.type"] = function ($c) {
            return new Filter\TypeFactory();
        };
        $container["filter.factory"] = function ($c) {
            return new Filter\FilterFactory(
                $c["filter.factory.predicate"],
                $c["filter.factory.type"]
            );
        };
        $container["filter.display"] = function ($c) {
            return new Filter\DisplayFilter(
                $c["filter.factory.gui"],
                $c["filter.factory.type"]
            );
        };
        $container["filter.factory.gui"] = function ($c) {
            return new Filter\FilterGUIFactory();
        };
        $container["filter.factory.view"] = function ($c) {
            return new \FilterViewFactory();
        };
        $container["factory.export"] = function ($c) {
            return new \ExportFactory();
        };
        $container["factory.report.tables"] = function ($c) {
            return new \ReportTableFactory();
        };
        $container["access.checker"] = function ($c) use ($object) {
            return new \AccessChecker(
                (int) $object->getRefId(),
                $c["ilAccess"]
            );
        };

        return $container;
    }
}

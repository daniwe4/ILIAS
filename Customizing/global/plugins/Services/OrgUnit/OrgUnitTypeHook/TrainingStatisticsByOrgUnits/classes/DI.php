<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\TrainingStatisticsByOrgUnits;

use Pimple\Container;
use ILIAS\TMS\TableRelations;
use ILIAS\TMS\Filter;
use CaT\Libs\ExcelWrapper\Spout\SpoutWriter;

trait DI
{
    public function getPluginDIC(
        \ilTrainingStatisticsByOrgUnitsPlugin $plugin,
        \ArrayAccess $dic
    ) : Container {
        $container = new Container();

        $container["ilCtrl"] = function ($c) use ($dic) {
            return $dic["ilCtrl"];
        };

        $container["report.singleview.gui.link"] = function ($c) {
            require_once __DIR__ . "/Report/SingleView/class.ilTSBOUSingleViewGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                [
                    "ilObjPluginDispatchGUI",
                    "ilObjTrainingStatisticsByOrgUnitsGUI",
                    "ilTSBOUSingleViewGUI"
                ],
                \ilTSBOUSingleViewGUI::CMD_SHOW_SINGLE_VIEW,
                "",
                false,
                false
            );
        };

        return $container;
    }

    public function getObjectDIC(
        \ilObjTrainingStatisticsByOrgUnits $object,
        \ArrayAccess $dic
    ) : Container {
        $container = new Container();

        $container["ilCtrl"] = function ($c) use ($dic) {
            return $dic["ilCtrl"];
        };

        $container["ilTabs"] = function ($c) use ($dic) {
            return $dic["ilTabs"];
        };

        $container["ilDB"] = function ($c) use ($dic) {
            return $dic["ilDB"];
        };

        $container["ilAccess"] = function ($c) use ($dic) {
            return $dic["ilAccess"];
        };

        $container["tpl"] = function ($c) use ($dic) {
            return $dic["tpl"];
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

        $container["treeobjectdiscovery"] = function ($c) {
            return new \ilTreeObjectDiscovery(
                $c["tree"]
            );
        };

        $container["spout.writer"] = function ($c) {
            return new SpoutWriter();
        };

        $container["info.link"] = function ($c) {
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilInfoScreenGUI",
                "showSummary",
                "",
                false,
                false
            );
        };

        $container["settings.gui"] = function ($c) use ($object) {
            require_once __DIR__ . "/Settings/class.ilTSBOUSettingsGUI.php";
            return new \ilTSBOUSettingsGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["txtclosure"],
                $object
            );
        };

        $container["settings.gui.link"] = function ($c) {
            require_once __DIR__ . "/Settings/class.ilTSBOUSettingsGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilTSBOUSettingsGUI",
                \ilTSBOUSettingsGUI::CMD_SHOW_SETTINGS,
                "",
                false,
                false
            );
        };

        $container["settings.db"] = function ($c) {
            return new Settings\ilDB(
                $c["ilDB"]
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

        $container["report.singleview"] = function ($c) use ($object) {
            return new Report\SingleView\Report(
                $object,
                $c["ilDB"],
                $c["txtclosure"],
                $c["filter.factory.predicate"],
                $c["table.factory"],
                $c["filter.factory.type"],
                $c["filter.factory"],
                $c["treeobjectdiscovery"],
                $c["settings.db"]->selectSettingsFor((int) $object->getId()),
                $c["plugindir"],
                $c["parent.id.ref"]
            );
        };

        $container["report.singleview.gui.link"] = function ($c) use ($object) {
            require_once __DIR__ . "/Report/SingleView/class.ilTSBOUSingleViewGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                [
                    "ilTSBOUSingleViewGUI"
                ],
                \ilTSBOUSingleViewGUI::CMD_SHOW_SINGLE_VIEW,
                "",
                false,
                false
            );
        };

        $container["report.singleview.gui"] = function ($c) use ($object) {
            require_once __DIR__ . "/Report/SingleView/class.ilTSBOUSingleViewGUI.php";
            return new \ilTSBOUSingleViewGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["spout.writer"],
                $c["report.singleview"],
                (int) $object->getRefId(),
                $c["parent.id.ref"]
            );
        };

        $container["report.splittedview"] = function ($c) use ($object) {
            return new Report\SplittedView\Report(
                $object,
                $c["ilDB"],
                $c["txtclosure"],
                $c["filter.factory.predicate"],
                $c["table.factory"],
                $c["filter.factory.type"],
                $c["filter.factory"],
                $c["treeobjectdiscovery"],
                $c["settings.db"]->selectSettingsFor((int) $object->getId()),
                $c["plugindir"],
                $c["parent.id.ref"]
            );
        };

        $container["report.splittedview.gui.link"] = function ($c) use ($object) {
            require_once __DIR__ . "/Report/SplittedView/class.ilTSBOUSplittedViewGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                [
                    "ilTSBOUSplittedViewGUI"
                ],
                \ilTSBOUSplittedViewGUI::CMD_SHOW_SPLITTED_VIEW,
                "",
                false,
                false
            );
        };

        $container["report.splittedview.gui"] = function ($c) use ($object) {
            require_once __DIR__ . "/Report/SplittedView/class.ilTSBOUSplittedViewGUI.php";
            return new \ilTSBOUSplittedViewGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["spout.writer"],
                $c["report.splittedview"],
                $c["txtclosure"],
                (int) $object->getRefId(),
                $c["parent.id.ref"]
            );
        };

        $container["parent.id.ref"] = function ($c) use ($object) {
            $is_global = $object->getSettings()->isGlobal();
            if (!$is_global) {
                $parent = $c["treeobjectdiscovery"]->getParentOfObjectOfType($object, 'orgu');
                $parent_ref_id = null;
                if (!is_null($parent)) {
                    $parent_ref_id = (int) $parent->getRefId();
                }
            } else {
                $parent_ref_id = (int) \ilObjOrgUnit::getRootOrgRefId();
            }
            return $parent_ref_id;
        };

        return $container;
    }
}

<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\Accounting;

use Pimple\Container;
use CaT\Libs\ExcelWrapper\Spout;

trait DI
{
    public function getPluginDIC(
        \ilAccountingPlugin $plugin,
        \ArrayAccess $dic
    ) : Container {
        $container = new Container();

        $container["ilDB"] = function ($c) use ($dic) {
            return $dic["ilDB"];
        };
        $container["ilCtrl"] = function ($c) use ($dic) {
            return $dic["ilCtrl"];
        };
        $container["ilTabs"] = function ($c) use ($dic) {
            return $dic["ilTabs"];
        };
        $container["ilToolbar"] = function ($c) use ($dic) {
            return $dic["ilToolbar"];
        };
        $container["tpl"] = function ($c) use ($dic) {
            return $dic["tpl"];
        };
        $container["tree"] = function ($c) use ($dic) {
            return $dic["tree"];
        };
        $container["ilAppEventHandler"] = function ($c) use ($dic) {
            return $dic["ilAppEventHandler"];
        };
        $container["rbacreview"] = function ($c) use ($dic) {
            return $dic["rbacreview"];
        };
        $container["ilSetting"] = function ($c) use ($dic) {
            return $dic["ilSetting"];
        };

        $container["actions"] = function ($c) use ($plugin) {
            return $plugin->getPluginActions();
        };

        $container["txtclosure"] = function ($c) use ($plugin) {
            return function ($code) use ($plugin) {
                return $plugin->txt($code);
            };
        };

        $container["plugin.path"] = function ($c) use ($plugin) {
            return $plugin->getDirectory();
        };

        $container["config.cancellation.scale.db"] = function ($c) {
            return new Config\Cancellation\Scale\ilDB(
                $c["ilDB"]
            );
        };

        $container["config.cancellation.scale.backend"] = function ($c) {
            return new Config\Cancellation\Scale\ScaleBackend(
                $c["config.cancellation.scale.db"]
            );
        };

        $container["config.cancellation.scale.tableprocessor"] = function ($c) {
            return new TableProcessing\TableProcessor(
                $c["config.cancellation.scale.backend"]
            );
        };

        $container["config.cancellation.scale.gui.link"] = function ($c) {
            require_once __DIR__ . "/Config/Cancellation/Scale/class.ilScaleGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                [
                    "ilCancellationGUI",
                    "ilScaleGUI"
                ],
                \ilScaleGUI::CMD_SHOW_SCALES,
                "",
                false,
                false
            );
        };

        $container["config.cancellation.scale.gui"] = function ($c) {
            require_once __DIR__ . "/Config/Cancellation/Scale/class.ilScaleGUI.php";
            return new \ilScaleGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["ilToolbar"],
                $c["txtclosure"],
                $c["config.cancellation.scale.db"],
                $c["config.cancellation.scale.tableprocessor"],
                $c["plugin.path"]
            );
        };

        $container["config.cancellation.gui"] = function ($c) {
            require_once __DIR__ . "/Config/Cancellation/class.ilCancellationGUI.php";
            return new \ilCancellationGUI(
                $c["ilCtrl"],
                $c["ilTabs"],
                $c["txtclosure"],
                $c["config.cancellation.scale.gui.link"],
                $c["config.cancellation.scale.gui"],
                $c["config.cancellation.roles.gui.link"],
                $c["config.cancellation.roles.gui"],
                $c["config.cancellation.states.gui.link"],
                $c["config.cancellation.states.gui"]
            );
        };

        $container["config.costtype.backend"] = function ($c) {
            return new Config\CostType\CostTypeBackend(
                $c["actions"]
            );
        };

        $container["config.costtype.tableprocessor"] = function ($c) {
            return new TableProcessing\TableProcessor(
                $c["config.costtype.backend"]
            );
        };

        $container["config.costtype.gui.link"] = function ($c) {
            require_once(__DIR__ . "/Config/CostType/class.ilCostTypeGUI.php");
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilCostTypeGUI",
                \ilCostTypeGUI::CMD_SHOW_COSTTYPE,
                "",
                false,
                false
            );
        };

        $container["config.costtype.gui"] = function ($c) {
            require_once(__DIR__ . "/Config/CostType/class.ilCostTypeGUI.php");
            return new \ilCostTypeGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["ilToolbar"],
                $c["plugin.path"],
                $c["actions"],
                $c["txtclosure"],
                $c["config.costtype.tableprocessor"]
            );
        };

        $container["config.vatrate.backend"] = function ($c) {
            return new Config\VatRate\VatRateBackend(
                $c["actions"]
            );
        };

        $container["config.vatrate.tableprocessor"] = function ($c) {
            return new TableProcessing\TableProcessor(
                $c["config.vatrate.backend"]
            );
        };

        $container["config.vatrate.gui.link"] = function ($c) {
            require_once(__DIR__ . "/Config/VatRate/class.ilVatRateGUI.php");
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilVatRateGUI",
                \ilVatRateGUI::CMD_SHOW_VATRATE,
                "",
                false,
                false
            );
        };

        $container["config.vatrate.gui"] = function ($c) {
            require_once(__DIR__ . "/Config/VatRate/class.ilVatRateGUI.php");
            return new \ilVatRateGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["ilToolbar"],
                $c["tree"],
                $c["ilAppEventHandler"],
                $c["txtclosure"],
                $c["plugin.path"],
                $c["actions"],
                $c["config.vatrate.tableprocessor"]
            );
        };

        $container["config.cancellation.roles.db"] = function ($c) {
            return new Config\Cancellation\Roles\ilDB(
                $c["ilSetting"]
            );
        };

        $container["config.cancellation.roles.gui.link"] = function ($c) {
            require_once __DIR__ . "/Config/Cancellation/Roles/class.ilCancellationRolesGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                [
                    "ilCancellationGUI",
                    "ilCancellationRolesGUI"
                ],
                \ilCancellationRolesGUI::CMD_SHOW_ROLES,
                "",
                false,
                false
            );
        };

        $container["config.cancellation.roles.gui"] = function ($c) {
            require_once __DIR__ . "/Config/Cancellation/Roles/class.ilCancellationRolesGUI.php";
            return new \ilCancellationRolesGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["rbacreview"],
                $c["txtclosure"],
                $c["config.cancellation.roles.db"]
            );
        };

        $container["config.cancellation.states.db"] = function ($c) {
            return new Config\Cancellation\ParticipantStatus\ilDB(
                $c["ilSetting"]
            );
        };

        $container["config.cancellation.states.gui.link"] = function ($c) {
            require_once __DIR__ . "/Config/Cancellation/ParticipantStatus/class.ilCancellationParticipantStatusGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                [
                    "ilCancellationGUI",
                    "ilCancellationParticipantStatusGUI"
                ],
                \ilCancellationParticipantStatusGUI::CMD_SHOW_STATES,
                "",
                false,
                false
            );
        };

        $container["config.cancellation.states.gui"] = function ($c) {
            require_once __DIR__ . "/Config/Cancellation/ParticipantStatus/class.ilCancellationParticipantStatusGUI.php";
            return new \ilCancellationParticipantStatusGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["rbacreview"],
                $c["txtclosure"],
                $c["config.cancellation.states.db"]
            );
        };

        $container["fees.cancel.db"] = function ($c) {
            return new Fees\CancellationFee\ilDB(
                $c["ilDB"]
            );
        };

        return $container;
    }

    public function getObjectDIC(
        \ilObjAccounting $object,
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
        $container["ilUser"] = function ($c) use ($dic) {
            return $dic["ilUser"];
        };
        $container["ilToolbar"] = function ($c) use ($dic) {
            return $dic["ilToolbar"];
        };
        $container["rbacreview"] = function ($c) use ($dic) {
            return $dic["rbacreview"];
        };
        $container["ilSetting"] = function ($c) use ($dic) {
            return $dic["ilSetting"];
        };
        $container["ilAppEventHandler"] = function ($c) use ($dic) {
            return $dic["ilAppEventHandler"];
        };
        $container["lng"] = function ($c) use ($dic) {
            return $dic["lng"];
        };
        $container["txtclosure"] = function ($c) use ($object) {
            return $object->txtClosure();
        };

        $container["plugin.folder"] = function ($c) use ($object) {
            return $object->directory();
        };

        $container["plugin.prefix"] = function ($c) use ($object) {
            return $object->prefix();
        };

        $container["actions"] = function ($c) use ($object) {
            return $object->getObjectActions();
        };

        $container["parentcrs"] = function ($c) {
            return $c["actions"]->getParentCourse();
        };

        $container["spout.write"] = function ($c) {
            return new Spout\SpoutWriter();
        };

        $container["spout.interpreter"] = function ($c) {
            return new Spout\SpoutInterpreter();
        };

        $container["data.excelexport"] = function ($c) {
            $crs_ref_id = "";
            if (!is_null($c["parentcrs"])) {
                $crs_ref_id = $c["parentcrs"]->getRefId();
            }

            $file_name = "/Kosten_" . $crs_ref_id . ".xlsx";
            return new Data\Export\ExportData(
                $file_name,
                sys_get_temp_dir(),
                $c["spout.write"],
                $c["spout.interpreter"],
                $c["txtclosure"]
            );
        };

        $container["costtype.db"] = function ($c) {
            return new Config\CostType\ilDB(
                $c["ilDB"],
                $c["ilUser"]
            );
        };

        $container["vatrate.db"] = function ($c) {
            return new Config\VatRate\ilDB(
                $c["ilDB"],
                $c["ilUser"]
            );
        };

        $container["fees.gui"] = function ($c) {
            require_once __DIR__ . "/Fees/class.ilFeesGUI.php";
            return new \ilFeesGUI(
                $c["ilCtrl"],
                $c["ilTabs"],
                $c["txtclosure"],
                $c["fees.cancel.gui.link"],
                $c["fees.cancel.gui"],
                $c["fees.fee.gui.link"],
                $c["fees.fee.gui"]
            );
        };

        $container["fees.cancel.gui.link"] = function ($c) {
            require_once __DIR__ . "/Fees/CancellationFee/class.ilCancellationFeeGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                [
                    "ilFeesGUI",
                    "ilCancellationFeeGUI"
                ],
                \ilCancellationFeeGUI::CMD_SHOW_CANCELLATION_FEE_SETTINGS,
                "",
                false,
                false
            );
        };

        $container["fees.cancel.db"] = function ($c) {
            return new Fees\CancellationFee\ilDB(
                $c["ilDB"]
            );
        };

        $container["fees.cancel.gui"] = function ($c) {
            require_once __DIR__ . "/Fees/CancellationFee/class.ilCancellationFeeGUI.php";
            return new \ilCancellationFeeGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["ilAppEventHandler"],
                $c["fees.cancel.db"],
                $c["txtclosure"]
            );
        };

        $container["fees.fee.gui.link"] = function ($c) {
            require_once __DIR__ . "/Fees/Fee/class.ilFeeGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                [
                    "ilFeesGUI",
                    "ilFeeGUI"
                ],
                \ilFeeGUI::CMD_SHOW_FEE_SETTINGS,
                "",
                false,
                false
            );
        };

        $container["fees.fee.db"] = function ($c) {
            return new Fees\Fee\ilDB(
                $c["ilDB"]
            );
        };

        $container["fees.fee.gui"] = function ($c) {
            require_once __DIR__ . "/Fees/Fee/class.ilFeeGUI.php";
            return new \ilFeeGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["ilAppEventHandler"],
                $c["fees.fee.db"],
                $c["txtclosure"]
            );
        };

        $container["settings.gui.link"] = function ($c) {
            require_once __DIR__ . "/Settings/class.ilSettingsGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilSettingsGUI",
                \ilSettingsGUI::CMD_EDIT_SETTINGS,
                "",
                false,
                false
            );
        };

        $container["settings.gui"] = function ($c) {
            require_once __DIR__ . "/Settings/class.ilSettingsGUI.php";
            return new \ilSettingsGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["ilAccess"],
                $c["actions"],
                $c["txtclosure"]
            );
        };

        $container["data.gui.link"] = function ($c) {
            require_once __DIR__ . "/Data/class.ilDataGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilDataGUI",
                \ilDataGUI::CMD_SHOW_CONTENT,
                "",
                false,
                false
            );
        };

        $container["data.gui"] = function ($c) {
            $c["lng"]->loadLanguageModule("form");
            require_once __DIR__ . "/Data/class.ilDataGUI.php";
            return new \ilDataGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["ilToolbar"],
                $c["ilAccess"],
                $c["txtclosure"],
                $c["actions"],
                $c["costtype.db"],
                $c["vatrate.db"],
                $c["plugin.folder"],
                $c["plugin.prefix"],
                $c["data.excelexport"]
            );
        };

        return $container;
    }
}

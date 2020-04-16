<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WBDCommunicator;

use Pimple\Container;
use ILIAS\TMS\TableRelations;
use ILIAS\TMS\Filter;
use CaT\WBD;
use ILIAS\TMS\WBD\Cases;
use CaT\Plugins\WBDCommunicator\SOAP;
use CaT\Security\PluginLogin\ILIAS;

trait DI
{
    public function getPluginDIC(
        \ilWBDCommunicatorPlugin $plugin,
        \ArrayAccess $dic
    ) : Container {
        $container = new Container();

        $container["ilTabs"] = function ($c) use ($dic) {
            return $dic["ilTabs"];
        };

        $container["ilCtrl"] = function ($c) use ($dic) {
            return $dic["ilCtrl"];
        };

        $container["tpl"] = function ($c) use ($dic) {
            return $dic["tpl"];
        };

        $container["ilSetting"] = function ($c) use ($dic) {
            return $dic["ilSetting"];
        };

        $container["ilUser"] = function ($c) use ($dic) {
            return $dic["ilUser"];
        };

        $container["ilDB"] = function ($c) use ($dic) {
            return $dic["ilDB"];
        };

        $container["ilUser"] = function ($c) use ($dic) {
            return $dic["ilUser"];
        };

        $container["ilLog"] = function ($c) use ($dic) {
            return $dic["ilLog"];
        };

        $container["ilAppEventHandler"] = function ($c) use ($dic) {
            return $dic["ilAppEventHandler"];
        };

        $container["txtclosure"] = function ($c) use ($plugin) {
            return function ($code) use ($plugin) {
                return $plugin->txt($code);
            };
        };

        $container["admin.plugin.link"] = function ($c) {
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilobjcomponentsettingsgui",
                "view",
                "",
                false,
                false
            );
        };

        $container["plugin.id"] = function ($c) use ($plugin) {
            return $plugin->getId();
        };

        $container["security.db"] = function ($c) {
            return new ILIAS\ilDB(
                $c["ilDB"]
            );
        };

        $container["security.gui.link"] = function ($c) use ($plugin) {
            require_once __DIR__ . "/Security/class.ilWBDCSecurityGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilWBDCSecurityGUI",
                \ilWBDCSecurityGUI::CMD_SHOW_CONFIG
            );
        };

        $container["security.gui"] = function ($c) use ($plugin) {
            require_once __DIR__ . "/Security/class.ilWBDCSecurityGUI.php";
            return new \ilWBDCSecurityGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["txtclosure"],
                $c["plugin.id"],
                $c["security.db"]
            );
        };

        $container["jobs.get"] = function ($c) {
            return function ($job_id) use ($c) {
                switch ($job_id) {
                    case Jobs\ilRequestParticipationsJob::ID:
                        return $c["job.requestparticipations"];
                    case Jobs\ilReportParticipationsJob::ID:
                        return $c["job.reportparticipations"];
                    case Jobs\ilCancelParticipationsJob::ID:
                        return $c["job.cancelparticipations"];
                }
            };
        };

        $container["jobs.getall"] = function ($c) {
            $jobs = [];
            try {
                $jobs[] = $c["job.requestparticipations"];
            } catch (\LogicException $e) {
                $c["ilLog"]->dump("Request job not created. " . $e->getMessage());
            }
            try {
                $jobs[] = $c["job.reportparticipations"];
            } catch (\LogicException $e) {
                $c["ilLog"]->dump("Report job not created. " . $e->getMessage());
            }
            try {
                $jobs[] = $c["job.cancelparticipations"];
            } catch (\LogicException $e) {
                $c["ilLog"]->dump("Report job not created. " . $e->getMessage());
            }
            return $jobs;
        };

        $container["job.requestparticipations"] = function ($c) {
            return new Jobs\ilRequestParticipationsJob(
                $c["wbd.log"],
                $c["wbd.services"],
                $c["txtclosure"],
                $c["cases.db"],
                $c["responses.db"],
                $c["config.oplimits.db"],
                $c["config.udf.db"],
                $c["cron.manager"]
            );
        };

        $container["job.reportparticipations"] = function ($c) {
            return new Jobs\ilReportParticipationsJob(
                $c["wbd.log"],
                $c["wbd.services"],
                $c["txtclosure"],
                $c["cases.db"],
                $c["responses.db"],
                $c["config.udf.db"],
                $c["config.oplimits.db"],
                $c["cron.manager"]
            );
        };

        $container["job.cancelparticipations"] = function ($c) {
            return new Jobs\ilCancelParticipationsJob(
                $c["wbd.log"],
                $c["wbd.services"],
                $c["txtclosure"],
                $c["cases.db"],
                $c["responses.db"],
                $c["config.udf.db"],
                $c["config.oplimits.db"],
                $c["cron.manager"]
            );
        };

        $container["wbd.log"] = function ($c) {
            return new WBD\Log\ilLog(
                $c["ilLog"]
            );
        };

        $container["wbd.services"] = function ($c) {
            return new WBD\Services\WBDServices(
                $c["wbd.soap.factory"]->getWBD3SOAP(),
                $c["wbd.log"],
                $c["wbd.log.error"]
            );
        };

        $container["wbd.soap.factory"] = function ($c) {
            return new SOAP\Factory(
                $c["config.connection.db"],
                $c["config.tgic.db"],
                $c["config.system.db"]
            );
        };

        $container["config.oplimits.db"] = function ($c) {
            return new Config\OperationLimits\ilDB(
                $c["ilSetting"]
            );
        };

        $container["config.oplimits.gui"] = function ($c) {
            require_once __DIR__ . "/Config/OperationLimits/class.ilWBDOperationLimitsGUI.php";
            return new \ilWBDOperationLimitsGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["config.oplimits.db"],
                $c["txtclosure"]
            );
        };

        $container["config.oplimits.gui.link"] = function ($c) {
            require_once __DIR__ . "/Config/OperationLimits/class.ilWBDOperationLimitsGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilWBDOperationLimitsGUI",
                \ilWBDOperationLimitsGUI::CMD_SHOW,
                "",
                false,
                false
            );
        };

        $container["config.tgic.db"] = function ($c) {
            return new Config\Tgic\ilDB(
                $c["ilDB"],
                $c["ilUser"]
            );
        };

        $container["confog.tgic.filestorage"] = function ($c) {
            return new Config\Tgic\FileStorage(0);
        };

        $container["config.tgic.gui"] = function ($c) {
            require_once __DIR__ . "/Config/Tgic/class.ilTgicGUI.php";
            return new \ilTgicGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["config.tgic.db"],
                $c["confog.tgic.filestorage"],
                $c["txtclosure"]
            );
        };

        $container["config.tgic.gui.link"] = function ($c) {
            require_once __DIR__ . "/Config/Tgic/class.ilTgicGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilTgicGUI",
                \ilTgicGUI::CMD_SHOW,
                "",
                false,
                false
            );
        };

        $container["config.udf.db"] = function ($c) {
            return new Config\UDF\ilDB(
                $c["ilDB"],
                $c["ilUser"]
            );
        };

        $container["config.udf.gui"] = function ($c) {
            require_once __DIR__ . "/Config/UDF/class.ilWBDUDFGUI.php";
            return new \ilWBDUDFGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["config.udf.db"],
                $c["txtclosure"]
            );
        };

        $container["config.udf.gui.link"] = function ($c) {
            require_once __DIR__ . "/Config/UDF/class.ilWBDUDFGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilWBDUDFGUI",
                \ilWBDUDFGUI::CMD_SHOW,
                "",
                false,
                false
            );
        };

        $container["config.connection.db"] = function ($c) {
            return new Config\Connection\ilDB(
                $c["ilDB"],
                $c["ilUser"]
            );
        };

        $container["config.connection.gui"] = function ($c) {
            require_once __DIR__ . "/Config/Connection/class.ilWBDConnectionGUI.php";
            return new \ilWBDConnectionGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["config.connection.db"],
                $c["txtclosure"]
            );
        };

        $container["config.connection.gui.link"] = function ($c) {
            require_once __DIR__ . "/Config/Connection/class.ilWBDConnectionGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilWBDConnectionGUI",
                \ilWBDConnectionGUI::CMD_SHOW,
                "",
                false,
                false
            );
        };

        $container["contact.db"] = function ($c) {
            require_once "Services/TMS/WBD/Contact/ilContactDB.php";
            return new \ilContactDB();
        };

        $container["cases.db"] = function ($c) {
            require_once "Services/TMS/WBD/Cases/ilCasesDB.php";
            return new \ilCasesDB(
                $c["ilDB"],
                $c["table.relations.sqlinterpreter"],
                $c["table.relations.factory.table"],
                $c["filter.factory.predicate"],
                $c["filter.factory.type"],
                $c["wbd.log.error"],
                $c["contact.db"]
            );
        };

        $container["responses.db"] = function ($c) {
            require_once "Services/TMS/WBD/Responses/ilResponsesDB.php";
            return new \ilResponsesDB(
                $c["ilDB"],
                $c["ilAppEventHandler"]
            );
        };

        $container["table.relations.sqlinterpreter"] = function ($c) {
            return new TableRelations\SqlQueryInterpreter(
                $c["filter.sqlinterpreter"],
                $c["filter.factory.predicate"],
                $c["ilDB"]
            );
        };

        $container["table.relations.factory.graph"] = function ($c) {
            return new TableRelations\GraphFactory();
        };

        $container["filter.factory.predicate"] = function ($c) {
            return new Filter\PredicateFactory();
        };

        $container["filter.factory.type"] = function ($c) {
            return new Filter\TypeFactory();
        };

        $container["filter.factory.filter"] = function ($c) {
            return new Filter\FilterFactory(
                $c["filter.factory.predicate"],
                $c["filter.factory.type"]
            );
        };

        $container["table.relations.factory.table"] = function ($c) {
            return new TableRelations\TableFactory(
                $c["filter.factory.predicate"],
                $c["table.relations.factory.graph"]
            );
        };

        $container["filter.sqlinterpreter"] = function ($c) {
            return new Filter\SqlPredicateInterpreter($c["ilDB"]);
        };

        $container["wbd.log.error"] = function ($c) {
            return new WBD\ErrorLog\ilDB(
                $c["ilDB"]
            );
        };

        $container["cron.manager"] = function ($c) {
            return new Jobs\CronManager();
        };

        $container["config.system.db"] = function ($c) {
            return new Config\WBD\ilDB(
                $c["ilSetting"]
            );
        };

        $container["config.system.gui"] = function ($c) {
            require_once __DIR__ . "/Config/WBD/class.ilSystemConfigurationGUI.php";
            return new \ilSystemConfigurationGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["config.system.db"],
                $c["txtclosure"]
            );
        };

        $container["config.system.gui.link"] = function ($c) {
            require_once __DIR__ . "/Config/WBD/class.ilSystemConfigurationGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilSystemConfigurationGUI",
                \ilSystemConfigurationGUI::CMD_SHOW,
                "",
                false,
                false
            );
        };

        return $container;
    }
}

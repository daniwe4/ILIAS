<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\CourseMember;

use Pimple\Container;

trait DI
{
    public function getPluginDIC(
        \ilCourseMemberPlugin $plugin,
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
        $container["tpl"] = function ($c) use ($dic) {
            return $dic["tpl"];
        };
        $container["ilUser"] = function ($c) use ($dic) {
            return $dic["ilUser"];
        };
        $container["plugin.path"] = function ($c) use ($plugin) {
            return $plugin->getDirectory();
        };
        $container["ilToolbar"] = function ($c) use ($dic) {
            return $dic["ilToolbar"];
        };

        $container["txtclosure"] = function ($c) use ($plugin) {
            return function ($code) use ($plugin) {
                return $plugin->txt($code);
            };
        };

        $container["txtclosure"] = function ($c) use ($plugin) {
            return function ($code) use ($plugin) {
                return $plugin->txt($code);
            };
        };

        $container["lng"] = function ($c) use ($dic) {
            return $dic["lng"];
        };

        $container["udf"] = function ($c) {
            return \ilUserDefinedFields::_getInstance();
        };

        $container["ExportFields"] = function ($c) {
            return \ilExportFieldsInfo::_getInstanceByType("crs");
        };

        $container["PrivacySettings"] = function ($c) {
            return \ilPrivacySettings::_getInstance();
        };

        $container["config.notfinalized.db"] = function ($c) {
            return new Reminder\ilDB($c["ilDB"]);
        };

        $container["config.notfinalized.link"] = function ($c) {
            require_once __DIR__ . "/Reminder/class.ilNotFinalizedGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(array("ilNotFinalizedGUI"), \ilNotFinalizedGUI::CMD_SHOW_CONFIG);
        };

        $container["config.notfinalizedgui"] = function ($c) {
            require_once __DIR__ . "/Reminder/class.ilNotFinalizedGUI.php";
            return new \ilNotFinalizedGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["txtclosure"],
                $c["config.notfinalized.db"],
                $c["ilUser"]
            );
        };

        $container["SignatureList.ilActions"] = function ($c) use ($plugin) {
            return $plugin->getSiglistActions();
        };

        $container["SignatureList.ilSiglistConfigGUI"] = function ($c) use ($plugin) {
            require_once __DIR__ . "/SignatureList/class.ilSiglistConfigGUI.php";
            return new \ilSiglistConfigGUI(
                $c["ilCtrl"],
                $c["ilTabs"],
                $c["SignatureList.ilStaticConfigGUI"],
                $c["SignatureList.ilConfigurableOverviewGUI"],
                $plugin
            );
        };

        $container["SignatureList.ilConfigurableConfigGUI"] = function ($c) use ($plugin) {
            require_once __DIR__ . "/SignatureList/ConfigurableList/class.ilConfigurableConfigGUI.php";
            return new \ilConfigurableConfigGUI(
                $c["tpl"],
                $c["ilCtrl"],
                $c["lng"],
                $plugin,
                $c["SignatureList.AvailiableFields"],
                $c['SignatureList.ConfigurableListConfigRepo']
            );
        };

        $container["SignatureList.ilConfigurableOverviewGUI"] = function ($c) use ($plugin) {
            require_once __DIR__ . "/SignatureList/ConfigurableList/class.ilConfigurableOverviewGUI.php";
            return new \ilConfigurableOverviewGUI(
                $c["tpl"],
                $c["ilCtrl"],
                $c["lng"],
                $plugin,
                $c['SignatureList.ConfigurableListConfigRepo'],
                $c["SignatureList.ilConfigurableConfigGUI"]
            );
        };

        $container["SignatureList.ilStaticConfigGUI"] = function ($c) {
            require_once __DIR__ . "/SignatureList/StaticList/class.ilStaticConfigGUI.php";
            return new \ilStaticConfigGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["ilToolbar"],
                $c["SignatureList.ilActions"]
            );
        };

        $container["SignatureList.AvailiableFields"] = function ($c) {
            return new SignatureList\ConfigurableList\IliasAvailableFields(
                $c["ExportFields"],
                $c["udf"],
                $c["PrivacySettings"],
                $c["lng"]
            );
        };

        $container['SignatureList.ConfigurableListConfigRepo'] = function ($c) {
            return new SignatureList\ConfigurableList\DBConfigurableListConfigRepo(
                $c['ilDB'],
                $c["SignatureList.AvailiableFields"]
            );
        };

        return $container;
    }

    public function getObjectDIC(
        \ilObjCourseMember $object,
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
        $container["tpl"] = function ($c) use ($dic) {
            return $dic["tpl"];
        };
        $container["ilUser"] = function ($c) use ($dic) {
            return $dic["ilUser"];
        };
        $container["ilAppEventHandler"] = function ($c) use ($dic) {
            return $dic["ilAppEventHandler"];
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

        $container["lpmanager"] = function ($c) {
            return new LPSettings\LPManagerImpl();
        };

        $container["txtclosure"] = function ($c) use ($object) {
            return function ($code) use ($object) {
                return $object->pluginTxt($code);
            };
        };

        $container["members.backend"] = function ($c) {
            return new Members\MemberBackend(
                $c["actions"],
                $c["xetr.iddtime"]
            );
        };

        $container["members.tableprocessor"] = function ($c) {
            return new TableProcessing\TableProcessor(
                $c["members.backend"]
            );
        };

        $container["xetr.iddtime"] = function ($c) use ($object) {
            return $object->getConfiguredIDDLearningTime();
        };

        $container["actions"] = function ($c) use ($object) {
            return new ilObjActions(
                $object,
                $c["members.db"],
                $c["lpmanager"],
                $c["ilAppEventHandler"]
            );
        };

        $container["lp.actions"] = function ($c) use ($object) {
            return $object->getLPOptionActions();
        };

        $container["siglist.actions"] = function ($c) use ($object) {
            return $object->getSignatureListActions();
        };

        $container["members.db"] = function ($c) {
            return new Members\ilDB(
                $c["ilDB"],
                $c["ilUser"]
            );
        };

        $container["members.gui.link"] = function ($c) {
            require_once __DIR__ . "/Members/class.ilMembersGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilMembersGUI",
                \ilMembersGUI::CMD_SHOW_MEMBERS,
                "",
                false,
                false
            );
        };

        $container["members.gui"] = function ($c) {
            require_once __DIR__ . "/Members/class.ilMembersGUI.php";
            return new \ilMembersGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["ilAccess"],
                $c["ilToolbar"],
                $c["actions"],
                $c["lp.actions"],
                $c["members.tableprocessor"],
                $c["siglist.actions"],
                $c["txtclosure"],
                $c["xetr.iddtime"]
            );
        };

        return $container;
    }
}

<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\OnlineSeminar;

use Pimple\Container;

trait DI
{
    public function getPluginDIC(
        \ilOnlineSeminarPlugin $plugin,
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
        $container["ilUser"] = function ($c) use ($dic) {
            return $dic["ilUser"];
        };
        $container["tpl"] = function ($c) use ($dic) {
            return $dic["tpl"];
        };
        $container["ilSetting"] = function ($c) use ($dic) {
            return $dic["ilSetting"];
        };

        $container["txtclosure"] = function ($c) use ($plugin) {
            return function ($code) use ($plugin) {
                return $plugin->txt($code);
            };
        };

        $container["config.gui"] = function ($c) {
            require_once __DIR__ . "/Config/class.ilConfigOnlineSeminarGUI.php";
            return new \ilConfigOnlineSeminarGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["txtclosure"],
                $c["config.db"]
            );
        };

        $container["config.gui.link"] = function ($c) {
            require_once __DIR__ . "/Config/class.ilConfigOnlineSeminarGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilConfigOnlineSeminarGUI",
                \ilConfigOnlineSeminarGUI::CMD_SHOW_ENTRIES,
                "",
                false,
                false
            );
        };

        $container["config.db"] = function ($c) {
            return new Config\Config(
                $c["ilSetting"]
            );
        };

        $container["config.notfinalized.db"] = function ($c) {
            return new Config\Reminder\ilDB($c["ilDB"]);
        };

        $container["config.notfinalized.link"] = function ($c) {
            require_once __DIR__ . "/Config/Reminder/class.ilOnlineSeminarNotFinalizedGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(array("ilOnlineSeminarNotFinalizedGUI"), \ilOnlineSeminarNotFinalizedGUI::CMD_SHOW_CONFIG);
        };

        $container["config.notfinalized.gui"] = function ($c) {
            require_once __DIR__ . "/Config/Reminder/class.ilOnlineSeminarNotFinalizedGUI.php";
            return new \ilOnlineSeminarNotFinalizedGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["txtclosure"],
                $c["config.notfinalized.db"],
                $c["ilUser"]
            );
        };

        return $container;
    }
}

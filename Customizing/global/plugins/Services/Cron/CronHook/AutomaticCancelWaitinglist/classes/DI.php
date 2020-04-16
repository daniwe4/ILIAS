<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\AutomaticCancelWaitinglist;

use Pimple\Container;

trait DI
{
    public function getPluginDI(
        \ilAutomaticCancelWaitinglistPlugin $plugin,
        $dic
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
        $container["pluginpath"] = function ($c) use ($plugin) {
            return $plugin->getDirectory();
        };
        $container["txtclsoure"] = function ($c) use ($plugin) {
            return function ($code) use ($plugin) {
                return $plugin->txt($code);
            };
        };

        $container["db.crs"] = function ($c) {
            return new Database\ilDB($c["ilDB"]);
        };
        $container["db.log"] = function ($c) {
            return new Log\ilDB($c["ilDB"]);
        };

        $container["jobs.acwaiting"] = function ($c) {
            return new AutomaticCancelWaitinglistJob(
                $c["db.crs"],
                $c["db.log"]
            );
        };

        $container["log.successgui"] = function ($c) {
            require_once __DIR__ . "/Log/class.ilCancelSuccessGUI.php";
            return new \ilCancelSuccessGUI(
                $c["pluginpath"],
                $c["txtclsoure"],
                $c["ilCtrl"],
                $c["db.log"],
                $c["tpl"]
            );
        };

        $container["log.failgui"] = function ($c) {
            require_once __DIR__ . "/Log/class.ilCancelFailGUI.php";
            return new \ilCancelFailGUI(
                $c["pluginpath"],
                $c["txtclsoure"],
                $c["ilCtrl"],
                $c["db.log"],
                $c["tpl"]
            );
        };

        return $container;
    }
}

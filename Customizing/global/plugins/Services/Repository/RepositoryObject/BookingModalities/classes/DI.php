<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\BookingModalities;

use Pimple\Container;

trait DI
{
    public function getPluginDI(
        \ilBookingModalitiesPlugin $plugin,
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
        $container["ilUser"] = function ($c) use ($dic) {
            return $dic["ilUser"];
        };

        $container["plugin.path"] = function ($c) use ($plugin) {
            return $plugin->getDirectory();
        };

        $container["plugin.actions"] = function ($c) use ($plugin) {
            return $plugin->getActions();
        };

        $container["txtclosure"] = function ($c) use ($plugin) {
            return function ($code) use ($plugin) {
                return $plugin->txt($code);
            };
        };

        $container["config.minmember.db"] = function ($c) {
            return new Reminder\ilDB($c["ilDB"]);
        };

        $container["config.minmember.link"] = function ($c) {
            require_once __DIR__ . "/Reminder/class.ilMinMemberGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(array("ilBookingModalitiesConfigGUI", "ilMinMemberGUI"), \ilMinMemberGUI::CMD_SHOW_CONFIG);
        };

        $container["config.minmembergui"] = function ($c) {
            require_once __DIR__ . "/Reminder/class.ilMinMemberGUI.php";
            return new \ilMinMemberGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["txtclosure"],
                $c["config.minmember.db"],
                $c["ilUser"]
            );
        };

        return $container;
    }
}

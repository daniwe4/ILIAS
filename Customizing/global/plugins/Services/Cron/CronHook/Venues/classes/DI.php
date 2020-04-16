<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\Venues;

use Pimple\Container;

trait DI
{
    public function getPluginDIC(
        \ilVenuesPlugin $plugin,
        \ArrayAccess $dic
    ) : Container {
        $container = new Container();

        $container["plugin"] = $plugin;
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
        $container["ilDB"] = function ($c) use ($dic) {
            return $dic["ilDB"];
        };
        $container["tree"] = function ($c) use ($dic) {
            return $dic["tree"];
        };
        $container["ilSetting"] = function ($c) use ($dic) {
            return $dic["ilSetting"];
        };
        $container["directory"] = function ($c) {
            return $c["plugin"]->getDirectory();
        };

        $container["txtclosure"] = function ($c) {
            return function ($code) use ($c) {
                return $c["plugin"]->txt($code);
            };
        };

        $container["config.tags.venue.link"] = function ($c) {
            require_once __DIR__ . "/Tags/Venue/class.ilVenueTagsGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilVenueTagsGUI",
                \ilVenueTagsGUI::CMD_SHOW_TAGS,
                "",
                false,
                false
            );
        };

        $container["config.tags.venue.db"] = function ($c) {
            return new Tags\Venue\ilDB(
                $c["ilDB"]
            );
        };

        $container["config.tags.venue.gui"] = function ($c) {
            require_once __DIR__ . "/Tags/Venue/class.ilVenueTagsGUI.php";
            return $gui = new \ilVenueTagsGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["config.tags.venue.db"],
                $c["txtclosure"],
                $c["directory"]
            );
        };

        $container["config.tags.search.link"] = function ($c) {
            require_once __DIR__ . "/Tags/Search/class.ilSearchTagsGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilSearchTagsGUI",
                \ilSearchTagsGUI::CMD_SHOW_TAGS,
                "",
                false,
                false
            );
        };

        $container["config.tags.search.db"] = function ($c) {
            return new Tags\Search\ilDB(
                $c["ilDB"]
            );
        };

        $container["config.tags.search.gui"] = function ($c) {
            require_once __DIR__ . "/Tags/Search/class.ilSearchTagsGUI.php";
            return $gui = new \ilSearchTagsGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["config.tags.search.db"],
                $c["txtclosure"],
                $c["directory"]
            );
        };

        $container["config.venues.link"] = function ($c) {
            require_once __DIR__ . "/Venues/class.ilVenuesGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilVenuesGUI",
                \ilVenuesGUI::CMD_SHOW,
                "",
                false,
                false
            );
        };

        $container["config.venues.gui"] = function ($c) {
            require_once __DIR__ . "/Venues/class.ilVenuesGUI.php";
            return new \ilVenuesGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["ilToolbar"],
                $c["tree"],
                $c["plugin"]->getActions(),
                $c["txtclosure"],
                $c["venues.address.formhelper"],
                $c["venues.capacity.formhelper"],
                $c["venues.conditions.formhelper"],
                $c["venues.contact.formhelper"],
                $c["venues.costs.formhelper"],
                $c["venues.general.formhelper"],
                $c["venues.rating.formhelper"],
                $c["venues.service.formhelper"],
                $c["config.tags.venue.db"]
            );
        };

        $container["venues.db"] = function ($c) {
            return new Venues\ilDB(
                $c["ilDB"]
            );
        };

        $container["venues.assignments.db"] = function ($c) {
            return new VenueAssignment\ilDB(
                $c["ilDB"]
            );
        };

        $container["venues.address.formhelper"] = function ($c) {
            return new Venues\Address\FormHelper(
                $c["plugin"]->getActions(),
                $c["txtclosure"]
            );
        };

        $container["venues.address.db"] = function ($c) {
            return new Venues\Address\ilDB(
                $c["ilDB"]
            );
        };

        $container["venues.capacity.formhelper"] = function ($c) {
            return new Venues\Capacity\FormHelper(
                $c["plugin"]->getActions(),
                $c["txtclosure"]
            );
        };

        $container["venues.capacity.db"] = function ($c) {
            return new Venues\Capacity\ilDB(
                $c["ilDB"]
            );
        };

        $container["venues.conditions.formhelper"] = function ($c) {
            return new Venues\Conditions\FormHelper(
                $c["plugin"]->getActions(),
                $c["txtclosure"]
            );
        };

        $container["venues.conditions.db"] = function ($c) {
            return new Venues\Conditions\ilDB(
                $c["ilDB"]
            );
        };

        $container["venues.contact.formhelper"] = function ($c) {
            return new Venues\Contact\FormHelper(
                $c["plugin"]->getActions(),
                $c["txtclosure"]
            );
        };

        $container["venues.contact.db"] = function ($c) {
            return new Venues\Contact\ilDB(
                $c["ilDB"]
            );
        };

        $container["venues.costs.formhelper"] = function ($c) {
            return new Venues\Costs\FormHelper(
                $c["plugin"]->getActions(),
                $c["txtclosure"]
            );
        };

        $container["venues.costs.db"] = function ($c) {
            return new Venues\Costs\ilDB(
                $c["ilDB"]
            );
        };

        $container["venues.general.formhelper"] = function ($c) {
            return new Venues\General\FormHelper(
                $c["plugin"]->getActions(),
                $c["txtclosure"],
                $c["config.tags.venue.db"],
                $c["config.tags.search.db"]
            );
        };

        $container["venues.general.db"] = function ($c) {
            return new Venues\General\ilDB(
                $c["ilDB"]
            );
        };

        $container["venues.rating.formhelper"] = function ($c) {
            return new Venues\Rating\FormHelper(
                $c["plugin"]->getActions(),
                $c["txtclosure"]
            );
        };

        $container["venues.rating.db"] = function ($c) {
            return new Venues\Rating\ilDB(
                $c["ilDB"]
            );
        };

        $container["venues.service.formhelper"] = function ($c) {
            return new Venues\Service\FormHelper(
                $c["plugin"]->getActions(),
                $c["txtclosure"]
            );
        };

        $container["venues.service.db"] = function ($c) {
            return new Venues\Service\ilDB(
                $c["ilDB"]
            );
        };

        return $container;
    }
}

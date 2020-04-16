<?php

declare(strict_types=1);

namespace CaT\Plugins\CourseCreation;

use Pimple\Container;

trait DI
{
    public function getPluginDI(
        \ilCourseCreationPlugin $plugin,
        \ArrayAccess $dic
    ) : Container {
        $container = new Container();

        $container["txtclosure"] = function ($c) use ($plugin) {
            return function ($code) use ($plugin) {
                return $plugin->txt($code);
            };
        };

        $container["creationsettings.gui"] = $container->factory(
            function ($c) use ($plugin, $dic) {
                require_once __DIR__ . "/CreationSettings/class.ilCreationSettingsGUI.php";
                return new \ilCreationSettingsGUI(
                    $dic["ilCtrl"],
                    $dic["tpl"],
                    $dic["rbacreview"],
                    $c["actions"],
                    $plugin->txtClosure()
                );
            }
        );

        $container["creationsettings.db"] = $container->factory(
            function ($c) use ($dic) {
                return new CreationSettings\ilDB(
                    $dic["ilDB"],
                    $dic["ilUser"]
                );
            }
        );

        $container["actions"] = $container->factory(
            function ($c) use ($dic) {
                return new ilActions(
                    $c["request.db"],
                    $c["creationsettings.db"]
                );
            }
        );

        $container["request.db"] = $container->factory(
            function ($c) use ($dic) {
                return new ilRequestDB($dic["ilDB"]);
            }
        );

        $container["creationsettings.config.failedrecipients.db"] = function ($c) use ($dic) {
            return new Recipients\ilDB(
                $dic["ilSetting"]
            );
        };

        $container["creationsettings.config.failedrecipients.gui.link"] = function ($c) use ($dic) {
            require_once __DIR__ . "/Recipients/class.ilFailedCreationRecipientsGUI.php";
            return $dic["ilCtrl"]->getLinkTargetByClass(
                "ilFailedCreationRecipientsGUI",
                \ilFailedCreationRecipientsGUI::CMD_SHOW
            );
        };

        $container["creationsettings.config.failedrecipients.gui"] = function ($c) use ($dic) {
            require_once __DIR__ . "/Recipients/class.ilFailedCreationRecipientsGUI.php";
            return new \ilFailedCreationRecipientsGUI(
                $dic["ilCtrl"],
                $dic["tpl"],
                $c["txtclosure"],
                $c["creationsettings.config.failedrecipients.db"]
            );
        };

        return $container;
    }
}

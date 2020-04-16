<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\ScaledFeedback;

use CaT\Plugins\ScaledFeedback\Feedback\ilDB;
use CaT\Plugins\ScaledFeedback\LPSettings\LPManagerImpl;
use ilDimensionsGUI;
use ilSetsGUI;
use Pimple\Container;

trait DI
{
    public function getPluginDIC(
        \ilScaledFeedbackPlugin $plugin,
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
        $container["lng"] = function ($c) use ($dic) {
            return $dic["lng"];
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

        $container["txtclosure"] = function ($c) use ($plugin) {
            return function ($code) use ($plugin) {
                return $plugin->txt($code);
            };
        };

        $container["plugin.path"] = function ($c) use ($plugin) {
            return $plugin->getDirectory();
        };

        $container["config.db"] = function ($c) {
            return new Config\ilDB($c["ilDB"]);
        };

        $container["config.dimensions.gui.link"] = function ($c) {
            require_once __DIR__ . "/Config/Dimensions/class.ilDimensionsGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilDimensionsGUI",
                \ilDimensionsGUI::CMD_SHOW_DIMENSIONS,
                "",
                false,
                false
            );
        };

        $container["config.dimension.gui"] = function ($c) {
            require_once __DIR__ . "/Config/Dimensions/class.ilDimensionGUI.php";
            return new \ilDimensionGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["config.db"],
                $c["config.dimensions.gui.link"],
                $c["txtclosure"]
            );
        };

        $container["config.dimensions.gui"] = function ($c) {
            require_once __DIR__ . "/Config/Dimensions/class.ilDimensionsGUI.php";
            return new ilDimensionsGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["ilTabs"],
                $c["ilToolbar"],
                $c["config.db"],
                $c["plugin.path"],
                $c["config.dimension.gui"],
                $c["txtclosure"]
            );
        };

        $container["config.sets.gui.link"] = function ($c) {
            require_once __DIR__ . "/Config/Sets/class.ilSetsGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilSetsGUI",
                \ilSetsGUI::CMD_SHOW_SETS,
                "",
                false,
                false
            );
        };

        $container["config.sets.gui"] = function ($c) {
            require_once __DIR__ . "/Config/Sets/class.ilSetsGUI.php";
            return new ilSetsGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["ilTabs"],
                $c["ilToolbar"],
                $c["config.db"],
                $c["plugin.path"],
                $c["config.set.gui"],
                $c["txtclosure"]
            );
        };

        $container["config.set.gui.link"] = function ($c) {
            require_once __DIR__ . "/Config/Sets/class.ilSetGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                ["ilSetsGUI", "ilSetGUI"],
                \ilSetGUI::CMD_SHOW_SETS,
                "",
                false,
                false
            );
        };

        $container["config.set.gui"] = function ($c) {
            require_once __DIR__ . "/Config/Sets/class.ilSetGUI.php";
            return new \ilSetGUI(
                $c["ilCtrl"],
                $c["ilTabs"],
                $c["config.sets.gui.link"],
                $c["config.set.settings.gui"],
                $c["config.set.settings.gui.link"],
                $c["config.set.dimension.gui"],
                $c["config.set.dimension.gui.link"],
                $c["config.set.text.gui"],
                $c["config.set.text.gui.link"],
                $c["txtclosure"]
            );
        };

        $container["config.set.settings.gui.link"] = function ($c) {
            require_once __DIR__ . "/Config/Sets/class.ilSetSettingsGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                ["ilSetsGUI", "ilSetGUI", "ilSetSettingsGUI"],
                \ilSetSettingsGUI::CMD_EDIT_SET,
                "",
                false,
                false
            );
        };

        $container["config.set.settings.gui"] = function ($c) {
            return new \ilSetSettingsGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["lng"],
                $c["config.db"],
                $c["config.sets.gui.link"],
                $c["txtclosure"]
            );
        };

        $container["config.set.dimension.gui.link"] = function ($c) {
            require_once __DIR__ . "/Config/Sets/class.ilSetDimensionsGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                ["ilSetsGUI", "ilSetGUI", "ilSetDimensionsGUI"],
                \ilSetDimensionsGUI::CMD_SHOW_SET_DIMENSIONS,
                "",
                false,
                false
            );
        };

        $container["config.set.dimension.gui"] = function ($c) {
            return new \ilSetDimensionsGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["ilToolbar"],
                $c["lng"],
                $c["config.db"],
                $c["config.sets.gui.link"],
                $c["plugin.path"],
                $c["txtclosure"]
            );
        };

        $container["config.set.text.gui.link"] = function ($c) {
            require_once __DIR__ . "/Config/Sets/class.ilSetTextGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                ["ilSetsGUI", "ilSetGUI", "ilSetTextGUI"],
                \ilSetTextGUI::CMD_SHOW_SET_TEXT,
                "",
                false,
                false
            );
        };

        $container["config.set.text.gui"] = function ($c) {
            return new \ilSetTextGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["lng"],
                $c["config.db"],
                $c["config.sets.gui.link"],
                $c["txtclosure"]
            );
        };

        return $container;
    }

    public function getObjectDIC(
        \ilObjScaledFeedback $object,
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

        $container["object"] = function ($c) use ($object) {
            return $object;
        };

        $container["config.db"] = function ($c) {
            return new Config\ilDB($c["ilDB"]);
        };

        $container["feedback.db"] = function ($c) {
            return new Feedback\ilDB($c["ilDB"]);
        };

        $container["settings.db"] = function ($c) {
            return new Settings\ilDB($c["ilDB"]);
        };

        $container["lpsettings.lpmanager"] = function ($c) {
            return new LPManagerImpl();
        };

        $container["scaled.feedback.gui.link"] = function ($c) {
            require_once __DIR__ . "/class.ilObjScaledFeedbackGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilObjScaledFeedbackGUI",
                \ilObjScaledFeedbackGUI::CMD_SHOW,
                "",
                false,
                false
            );
        };

        $container["feedback.gui.link"] = function ($c) {
            require_once __DIR__ . "/Feedback/class.ilFeedbackGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilFeedbackGUI",
                \ilFeedbackGUI::CMD_SHOW,
                "",
                false,
                false
            );
        };

        $container["feedback.gui"] = function ($c) {
            require_once __DIR__ . "/Feedback/class.ilFeedbackGUI.php";
            return new \ilFeedbackGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["ilUser"],
                $c["feedback.db"],
                $c["config.db"],
                $c["object"],
                $c["lpsettings.lpmanager"],
                $c["txtclosure"]
            );
        };

        $container["settings.gui.link"] = function ($c) {
            require_once __DIR__ . "/Settings/class.ilSFSettingsGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilSFSettingsGUI",
                \ilSFSettingsGUI::CMD_SHOW,
                "",
                false,
                false
            );
        };

        $container["settings.gui"] = function ($c) {
            require_once __DIR__ . "/Settings/class.ilSFSettingsGUI.php";
            return new \ilSFSettingsGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["feedback.db"],
                $c["config.db"],
                $c["object"],
                $c["scaled.feedback.gui.link"],
                $c["txtclosure"]
            );
        };

        $container["evaluation.gui.link"] = function ($c) {
            require_once __DIR__ . "/Evaluation/class.ilEvaluationGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilEvaluationGUI",
                \ilEvaluationGUI::CMD_SHOW_EVALUATION,
                "",
                false,
                false
            );
        };

        $container["evaluation.gui"] = function ($c) {
            require_once __DIR__ . "/Evaluation/class.ilEvaluationGUI.php";
            return new \ilEvaluationGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["feedback.db"],
                $c["config.db"],
                $c["object"],
                $c["txtclosure"]
            );
        };

        $container["lpsettings.gui.link"] = function ($c) {
            require_once __DIR__ . "/LPSettings/class.ilScaledFeedbackLPSettingsGUI.php";
            return $c["ilCtrl"]->getLinkTargetByClass(
                "ilScaledFeedbackLPSettingsGUI",
                \ilScaledFeedbackLPSettingsGUI::CMD_LP,
                "",
                false,
                false
            );
        };

        $container["lpsettings.gui"] = function ($c) {
            require_once __DIR__ . "/LPSettings/class.ilScaledFeedbackLPSettingsGUI.php";
            return new \ilScaledFeedbackLPSettingsGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["lng"],
                $c["object"],
                $c["lpsettings.lpmanager"],
                $c["txtclosure"]
            );
        };

        return $container;
    }
}

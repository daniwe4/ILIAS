<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\TrainingSearch;

use Pimple\Container;

trait DI
{
    public function getPluginDIC(
        \ilTrainingSearchPlugin $plugin,
        \ArrayAccess $dic
    ) : Container {
        $container = new Container();

        $container["ilDB"] = $dic["ilDB"];

        return $container;
    }

    public function getObjectDI(
        \ilObjTrainingSearch $object,
        \ArrayAccess $dic
    ) : Container {
        $container = new Container();

        $container["object"] = $object;
        $container["ilCtrl"] = $dic["ilCtrl"];
        $container["tpl"] = $dic["tpl"];
        $container["ilDB"] = $dic["ilDB"];
        $container["ilUser"] = $dic["ilUser"];
        $container["ilAccess"] = $dic["ilAccess"];
        $container["ilSetting"] = $dic["ilSetting"];
        $container["tree"] = $dic["tree"];
        $container["lng"] = $dic["lng"];
        $container["ui.factory"] = $dic["ui.factory"];
        $container["ui.renderer"] = $dic["ui.renderer"];

        $this->setupSettingsElements($container);
        $this->setupSearchElements($container);

        return $container;
    }

    protected function setupSettingsElements(Container $container)
    {
        $container["settings.db"] = function ($c) {
            return new Settings\ilDB($c["ilDB"]);
        };

        $container["settings.gui"] = function ($c) {
            require_once __DIR__ . "/Settings/class.ilTrainingSearchSettingsGUI.php";
            return new \ilTrainingSearchSettingsGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["object"]
            );
        };
    }

    protected function setupSearchElements(Container $container)
    {
        $container["search.db"] = function ($c) {
            if ($c["ilGlobalCache"]->isActive()) {
                return $c["search.ilCachingDB"];
            } else {
                return $c["search.ilDB"];
            }
        };

        $container["search.ilDB"] = function ($c) {
            return new Search\ilDB(
                $c["ilDB"],
                $c["ilAccess"],
                $c["tree"],
                $c["search.ilObjectFactory"]
            );
        };

        $container["search.ilCachingDB"] = function ($c) {
            return new Search\ilCachingDB(
                $c["search.ilDB"],
                $c["search.ilObjectFactory"],
                $c["search.cache"]
            );
        };

        $container["search.ilObjectFactory"] = function ($c) {
            return new Search\ilObjectFactory(
                $c["tree"]
            );
        };

        $container["search.cache"] = function ($c) {
            return new Search\ilGlobalCache(
                $c["ilGlobalCache"],
                \ilTrainingSearchPlugin::TTL_FOR_RESULTS_IN_APC_IN_S
            );
        };

        $container["ilGlobalCache"] = function ($c) {
            $global_cache = \ilGlobalCache::getInstance(\ilGlobalCache::COMP_SEARCH);
            return $global_cache;
        };

        $container["search.gui"] = function ($c) {
            require_once __DIR__ . "/Search/class.ilCoursesGUI.php";
            return new \ilCoursesGUI(
                $c["ilCtrl"],
                $c["tpl"],
                $c["ilUser"],
                $c["ui.factory"],
                $c["ui.renderer"],
                $c["object"],
                $c["search.db"],
                $c["search.pageedit"],
                $c["ilAccess"],
                $c["ilSetting"]
            );
        };

        $container["search.tablegui"] = function ($c) {
            return new Search\ilCoursesTableGUI(
                $c["ilCtrl"],
                $c["ilUser"],
                $c["ui.factory"],
                $c["ui.renderer"],
                $c["object"]->getSettings(),
                $c["object"]->getTxtClosure()
            );
        };

        $container["search.helper"] = function ($c) {
            return new Search\Helper(
                $c["ilUser"],
                $c["ilCtrl"],
                $c["lng"],
                $c["ui.factory"],
                $c["ui.renderer"]
            );
        };

        $container["search.pageedit"] = function ($c) {
            require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/TrainingSearch/classes/Page/class.ilTrainingSearchPageGUI.php";
            include_once "Services/Object/classes/class.ilObjectTranslation.php";
            $ot = \ilObjectTranslation::getInstance($c["object"]->getId());
            $lang = $ot->getEffectiveContentLang(
                $c["ilUser"]->getCurrentLanguage(),
                $c["object"]->getType()
            );
            $gui = new \ilTrainingSearchPageGUI(
                $c["object"]->getType(),
                $c["object"]->getId(),
                $c["tpl"],
                $lang
            );
            return $gui;
        };
    }
}

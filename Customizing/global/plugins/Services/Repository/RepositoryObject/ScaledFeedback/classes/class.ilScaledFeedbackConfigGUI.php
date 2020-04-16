<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

require_once "./Services/Component/classes/class.ilPluginConfigGUI.php";

use CaT\Plugins\ScaledFeedback\DI;
use Pimple\Container;

/**
 * GUI class to add or delete training provider, trainer or tags
 *
 * @ilCtrl_Calls ilScaledFeedbackConfigGUI: ilDimensionsGUI
 * @ilCtrl_Calls ilScaledFeedbackConfigGUI: ilSetsGUI
 */
class ilScaledFeedbackConfigGUI extends ilPluginConfigGUI
{
    use DI;

    const TAB_DIMENSIONS = "dimensions_gui";
    const TAB_SETS = "sets_gui";

    /**
     * @var Container
     */
    protected $dic;

    /**
     * @throws Exception
     */
    public function performCommand($cmd)
    {
        $this->setTabs();

        $next_class = $this->getDIC()["ilCtrl"]->getNextClass();

        switch ($next_class) {
            case "ildimensionsgui":
                $this->forwardDimensionsGUI();
                break;
            case "ilsetsgui":
                $this->forwardSetsGUI();
                break;
            default:
                switch ($cmd) {
                    case "configure":
                        $this->getDIC()["ilCtrl"]->redirectToURL($this->dic["config.dimensions.gui.link"]);
                        break;
                    default:
                        throw new Exception("unknown command " . $cmd);
                }
        }
    }

    protected function forwardDimensionsGUI()
    {
        $this->getDIC()["ilTabs"]->activateTab(self::TAB_DIMENSIONS);
        $gui = $this->getDIC()["config.dimensions.gui"];
        $this->getDIC()["ilCtrl"]->forwardCommand($gui);
    }

    protected function forwardSetsGUI()
    {
        $this->getDIC()["ilTabs"]->activateTab(self::TAB_SETS);
        $gui = $this->getDIC()["config.sets.gui"];
        $this->getDIC()["ilCtrl"]->forwardCommand($gui);
    }

    protected function setTabs()
    {
        $this->getDIC()["ilTabs"]->addTab(
            self::TAB_DIMENSIONS,
            $this->txt("dimensions"),
            $this->getDIC()["config.dimensions.gui.link"]
        );

        $this->getDIC()["ilTabs"]->addTab(
            self::TAB_SETS,
            $this->txt("sets"),
            $this->getDIC()["config.sets.gui.link"]
        );
    }

    protected function getDIC()
    {
        if (is_null($this->dic)) {
            global $DIC;
            $this->dic = $this->getPluginDIC($this->plugin_object, $DIC);
        }
        return $this->dic;
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->getDIC()["txtclosure"], $code);
    }
}

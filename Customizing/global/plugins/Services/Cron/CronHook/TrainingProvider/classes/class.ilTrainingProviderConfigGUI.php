<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Component/classes/class.ilPluginConfigGUI.php");

/**
 * GUI class to add or delete training provider, trainer or tags
 *
 * @ilCtrl_Calls ilTrainingProviderConfigGUI: ilProviderGUI
 * @ilCtrl_Calls ilTrainingProviderConfigGUI: ilTagsGUI
 * @ilCtrl_Calls ilTrainingProviderConfigGUI: ilTrainerGUI
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilTrainingProviderConfigGUI extends ilPluginConfigGUI
{
    const CMD_TRAINER = "showTrainer";
    const CMD_CONFIGURE = "configure";
    const CMD_TAGS = "showTags";

    /**
     * @var ilCtrl
     */
    protected $gCtrl;

    /**
     * @var ilTabsGUI
     */
    protected $gTabs;

    public function __construct()
    {
        global $ilCtrl, $ilTabs;
        $this->gCtrl = $ilCtrl;
        $this->gTabs = $ilTabs;
    }

    public function performCommand($cmd)
    {
        require_once($this->plugin_object->getDirectory() . "/classes/Provider/class.ilProviderGUI.php");
        require_once($this->plugin_object->getDirectory() . "/classes/Tags/class.ilTagsGUI.php");
        require_once($this->plugin_object->getDirectory() . "/classes/Trainer/class.ilTrainerGUI.php");

        $this->actions = $this->plugin_object->getActions();
        $this->setTabs();

        $next_class = $this->gCtrl->getNextClass();

        switch ($next_class) {
            case "ilprovidergui":
                $this->forwardProviderGUI();
                break;
            case "iltagsgui":
                $this->forwardTagsGUI();
                break;
            case "iltrainergui":
                $this->forwardTrainerGUI();
                break;
            default:
                switch ($cmd) {
                case self::CMD_CONFIGURE:
                    $this->forwardProviderGUI();
                    break;
                case self::CMD_TAGS:
                    $this->forwardTagsGUI();
                    break;
                case self::CMD_TRAINER:
                    $this->forwardTrainerGUI();
                    break;
                default:
                    throw new Exception("ilTrainingProviderConfigGUI:: Unknown command: " . $cmd);
            }
        }
    }

    protected function trainer()
    {
        $this->gTabs->activateTab(self::CMD_TRAINER);
    }

    protected function forwardProviderGUI()
    {
        $this->gTabs->activateTab(self::CMD_CONFIGURE);
        $gui = new ilProviderGUI($this, $this->plugin_object, $this->actions);
        $this->gCtrl->forwardCommand($gui);
    }

    protected function forwardTagsGUI()
    {
        $this->gTabs->activateTab(self::CMD_TAGS);
        $gui = new ilTagsGUI($this, $this->plugin_object, $this->actions);
        $this->gCtrl->forwardCommand($gui);
    }

    protected function forwardTrainerGUI()
    {
        $this->gTabs->activateTab(self::CMD_TRAINER);
        $gui = new ilTrainerGUI($this, $this->plugin_object, $this->actions);
        $this->gCtrl->forwardCommand($gui);
    }

    /**
     * Sets tabs for provider, trainer and tags
     */
    protected function setTabs()
    {
        $provider_link = $this->gCtrl->getLinkTarget($this, self::CMD_CONFIGURE);
        $trainer_link = $this->gCtrl->getLinkTarget($this, self::CMD_TRAINER);
        $tag_link = $this->gCtrl->getLinkTarget($this, self::CMD_TAGS);

        $this->gTabs->addTab(self::CMD_CONFIGURE, $this->plugin_object->txt("provider"), $provider_link);
        $this->gTabs->addTab(self::CMD_TRAINER, $this->plugin_object->txt("trainer"), $trainer_link);
        $this->gTabs->addTab(self::CMD_TAGS, $this->plugin_object->txt("tags"), $tag_link);
    }
}

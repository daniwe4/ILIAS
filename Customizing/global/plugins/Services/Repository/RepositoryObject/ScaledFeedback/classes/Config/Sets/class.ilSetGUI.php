<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

require_once __DIR__ . "/class.ilSetsGUI.php";
require_once __DIR__ . "/class.ilSetSettingsGUI.php";
require_once __DIR__ . "/class.ilSetDimensionsGUI.php";
require_once __DIR__ . "/class.ilSetTextGUI.php";

/**
 * @ilCtrl_Calls ilSetGUI: ilSetSettingsGUI, ilSetDimensionsGUI, ilSetTextGUI
 */
class ilSetGUI
{
    const CMD_SHOW_SETS = "showSets";

    const TAB_SET_SETTINGS = "tabSetSettings";
    const TAB_SET_DIMENSIONS = "tabSetDimenions";
    const TAB_SET_TEXT = "tabSetText";

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var string
     */
    protected $sets_gui_link;

    /**
     * @var ilSetSettingsGUI
     */
    protected $set_settings_gui;

    /**
     * @var string
     */
    protected $set_settings_gui_link;

    /**
     * @var ilSetDimensionsGUI
     */
    protected $set_dimensions_gui;

    /**
     * @var string
     */
    protected $set_dimensions_gui_link;

    /**
     * @var ilSetTextGUI
     */
    protected $set_text_gui;

    /**
     * @var string
     */
    protected $set_text_gui_link;

    /**
     * @var Closure
     */
    protected $txt;

    public function __construct(
        ilCtrl $ctrl,
        ilTabsGUI $tabs,
        string $sets_gui_link,
        ilSetSettingsGUI $set_settings_gui,
        string $set_settings_gui_link,
        ilSetDimensionsGUI $set_dimensions_gui,
        string $set_dimensions_gui_link,
        ilSetTextGUI $set_text_gui,
        string $set_text_gui_link,
        Closure $txt
    ) {
        $this->ctrl = $ctrl;
        $this->tabs = $tabs;
        $this->sets_gui_link = $sets_gui_link;
        $this->set_settings_gui = $set_settings_gui;
        $this->set_settings_gui_link = $set_settings_gui_link;
        $this->set_dimensions_gui = $set_dimensions_gui;
        $this->set_dimensions_gui_link = $set_dimensions_gui_link;
        $this->set_text_gui = $set_text_gui;
        $this->set_text_gui_link = $set_text_gui_link;
        $this->txt = $txt;
    }

    /**
     * @throws Exception
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCMD();
        $next_class = $this->ctrl->getNextClass();
        if (
            $cmd != ilSetSettingsGUI::CMD_ADD_SET &&
            $cmd != ilSetSettingsGUI::CMD_SAVE_SET_SETTINGS
        ) {
            $this->setTabs();
        }

        switch ($next_class) {
            case "ilsetsettingsgui":
                $this->forwardSetSettings($cmd);
                break;
            case "ilsetdimensionsgui":
                $this->forwardSetDimensions();
                break;
            case "ilsettextgui":
                $this->forwardSetText();
                break;
            default:
                throw new Exception(__METHOD__ . " unknown next class: " . $next_class);
        }
    }

    /**
     * @throws ilCtrlException
     */
    protected function forwardSetSettings(string $cmd)
    {
        if ($cmd != ilSetSettingsGUI::CMD_ADD_SET && $cmd != ilSetSettingsGUI::CMD_SAVE_SET_SETTINGS) {
            $this->activateTab(self::TAB_SET_SETTINGS);
        }
        $this->ctrl->forwardCommand($this->set_settings_gui);
    }

    /**
     * @throws ilCtrlException
     */
    protected function forwardSetDimensions()
    {
        $this->activateTab(self::TAB_SET_DIMENSIONS);
        $this->ctrl->forwardCommand($this->set_dimensions_gui);
    }

    /**
     * @throws ilCtrlException
     */
    protected function forwardSetText()
    {
        $this->activateTab(self::TAB_SET_TEXT);
        $this->ctrl->forwardCommand($this->set_text_gui);
    }

    protected function setTabs()
    {
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget($this->txt("back"), $this->sets_gui_link);

        $set_settings_link = $this->getLinkWithParam($this->set_settings_gui_link, $_GET["id"]);
        $set_dimensions_link = $this->getLinkWithParam($this->set_dimensions_gui_link, $_GET["id"]);
        $set_text_link = $this->getLinkWithParam($this->set_text_gui_link, $_GET["id"]);


        $this->tabs->addTab(
            self::TAB_SET_SETTINGS,
            $this->txt("set_settings"),
            $set_settings_link
        );

        $this->tabs->addTab(
            self::TAB_SET_DIMENSIONS,
            $this->txt("set_dimensions"),
            $set_dimensions_link
        );

        $this->tabs->addTab(
            self::TAB_SET_TEXT,
            $this->txt("set_text"),
            $set_text_link
        );
    }

    protected function getLinkWithParam(string $link, string $value = null)
    {
        if (is_null($value)) {
            return $link;
        }
        $link .= "&id=" . $value;
        return $link;
    }

    protected function activateTab(string $tab)
    {
        $this->tabs->activateTab($tab);
    }

    protected function txt(string $code) : string
    {
        $txt = $this->txt;
        return $txt($code);
    }
}

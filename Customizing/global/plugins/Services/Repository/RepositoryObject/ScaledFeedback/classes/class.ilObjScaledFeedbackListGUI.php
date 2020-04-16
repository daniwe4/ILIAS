<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

require_once "./Services/Repository/classes/class.ilObjectPluginListGUI.php";

/**
 * List gui class for plugin object in repository
 */
class ilObjScaledFeedbackListGUI extends ilObjectPluginListGUI
{
    /**
     * Init the type of the plugin. Same value as choosen in plugin.php
     */
    public function initType()
    {
        $this->setType("xfbk");
    }

    /**
     * Get name of gui class handling the commands
     */
    public function getGuiClass()
    {
        return "ilObjScaledFeedbackGUI";
    }

    /**
     * Get commands
     */
    public function initCommands()
    {
        return [
            [
                "permission" => "read",
                "cmd" => "showContent",
                "default" => true
            ],
            [
                "permission" => "write",
                "cmd" => "showContent",
                "txt" => $this->txt("edit"),
                "default" => false
            ]
        ];
    }

    protected function initListActions()
    {
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->info_screen_enabled = true;
        $this->copy_enabled = true;
    }

    /**
     * Get item properties
     */
    public function getProperties() : array
    {
        global $lng;

        $props = parent::getProperties();

        include_once __DIR__ . '/class.ilObjScaledFeedbackAccess.php';
        if (ilObjScaledFeedbackAccess::_isOffline($this->obj_id)) {
            $props[] = [
                "alert" => true,
                "property" => $lng->txt("status"),
                "value" => $lng->txt("offline")
            ];
        }
        return $props;
    }
}

<?php

require_once 'Services/Repository/classes/class.ilObjectPluginListGUI.php';

/**
* ListGUI implementation for Report object plugin. This one
* handles the presentation in container items (categories, courses, ...)
* together with the corresponfing  Report Access class.
*/
class ilObjAgendaListGUI extends ilObjectPluginListGUI
{
    /**
    * Get commands
    */
    public function initCommands()
    {
        require_once __DIR__ . "/Settings/class.ilAgendaSettingsGUI.php";
        return array(
            array(
                "permission" => "read",
                "cmd" => ilAgendaSettingsGUI::CMD_EDIT_PROPERTIES,
                "txt" => "show",
                "default" => true),
            array(
                "permission" => "write",
                "cmd" => ilAgendaSettingsGUI::CMD_EDIT_PROPERTIES,
                "txt" => $this->lng->txt("edit"),
                "default" => false)
        );
    }

    public function initType()
    {
        $this->setType("xage");
    }

    /**
    * Get name of gui class handling the commands
    */
    public function getGuiClass()
    {
        return "ilObjAgendaGUI";
    }

    public function getProperties()
    {
        return array();
    }
}

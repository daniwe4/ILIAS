<?php

require_once 'Services/Repository/classes/class.ilObjectPluginListGUI.php';

/**
* ListGUI implementation for Report object plugin. This one
* handles the presentation in container items (categories, courses, ...)
* together with the corresponfing  Report Access class.
*/
class ilObjEduTrackingListGUI extends ilObjectPluginListGUI
{
    /**
    * Get commands
    */
    public function initCommands()
    {
        require_once __DIR__ . "/Settings/class.ilEduTrackingSettingsGUI.php";
        return array(
            array(
                "permission" => "read",
                "cmd" => "showContent",
                "txt" => "show",
                "default" => true),
            array(
                "permission" => "write",
                "cmd" => ilEduTrackingSettingsGUI::CMD_EDIT_PROPERTIES,
                "txt" => $this->lng->txt("edit"),
                "default" => false)
        );
    }

    public function initType()
    {
        $this->setType("xetr");
    }

    /**
    * Get name of gui class handling the commands
    */
    public function getGuiClass()
    {
        return "ilObjEduTrackingGUI";
    }

    public function getProperties()
    {
        return array();
    }
}

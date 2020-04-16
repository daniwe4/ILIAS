<?php

declare(strict_types=1);

include_once("./Services/Repository/classes/class.ilObjectPluginListGUI.php");

/**
 * List gui class for plugin object in repository.
 *
 * @author
 * @copyright Extended GPL, see LICENSE
 */
class ilObjBookingApprovalsListGUI extends ilObjectPluginListGUI
{
    /**
     * Init the type of the plugin. Same value as choosen in plugin.php.
     *
     * @return 	void
     */
    public function initType()
    {
        $this->setType("xbka");
    }

    public function getGuiClass() : string
    {
        return "ilObjBookingApprovalsGUI";
    }


    public function initCommands() : array
    {
        return array(
            [
                "permission" => "read",
                "cmd" => "showContent",
                "default" => true
            ],
            [
                "permission" => "write",
                "cmd" => "editProperties",
                "txt" => $this->txt("edit"),
                "default" => false
            ]
        );
    }

    /**
     * Init the commands for the list actions.
     *
     * @return 	void
     */
    protected function initListActions()
    {
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->info_screen_enabled = true;
        $this->copy_enabled = true;
    }
}

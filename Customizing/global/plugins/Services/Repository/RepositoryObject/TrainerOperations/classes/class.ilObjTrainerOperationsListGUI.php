<?php

declare(strict_types=1);

use CaT\Plugins\TrainerOperations\ObjTrainerOperations;

/**
 * List gui class for plugin object in repository.
 *
 * @author
 * @copyright Extended GPL, see LICENSE
 */
class ilObjTrainerOperationsListGUI extends ilObjectPluginListGUI
{
    /**
     * Init the type of the plugin. Same value as choosen in plugin.php.
     *
     * @return 	void
     */
    public function initType()
    {
        $this->setType(ObjTrainerOperations::PLUGIN_ID);
    }

    /**
     * Get name of gui class handling the commands.
     *
     * @return 	ilObjTrainerOperationsGUI
     */
    public function getGuiClass()
    {
        return "ilObjTrainerOperationsGUI";
    }

    /**
     * Get commands.
     *
     * @return 	void
     */
    public function initCommands()
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
        $this->subscribe_enabled = false;
        $this->link_enabled = false;
        $this->info_screen_enabled = true;
        $this->copy_enabled = true;
    }
}

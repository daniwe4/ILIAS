<?php


use \CaT\Plugins\TrainingStatistics as TS;

include_once("./Services/Repository/classes/class.ilObjectPluginListGUI.php");

/**
 * List gui class for plugin object in repository.
 *
 * @author
 * @copyright Extended GPL, see LICENSE
 */
class ilObjTrainingStatisticsListGUI extends ilObjectPluginListGUI
{
    /**
     * Init the type of the plugin. Same value as choosen in plugin.php.
     *
     * @return 	void
     */
    public function initType()
    {
        $this->setType("xrts");
    }

    /**
     * Get name of gui class handling the commands.
     *
     * @return 	string
     */
    public function getGuiClass()
    {
        return "ilObjTrainingStatisticsGUI";
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
                "cmd" => "to_report",
                "default" => true
            ],
            [
                "permission" => "write",
                "cmd" => "to_settings",
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

    public function getProperties()
    {
        $props = array();

        $this->plugin->includeClass("class.ilObjTrainingStatisticsAccess.php");
        if (ilObjTrainingStatisticsAccess::_isOffline($this->obj_id)) {
            $props[] = array("alert" => true, "property" => $this->lng->txt("status"),
            "value" => $this->lng->txt("offline"));
        }

        return $props;
    }
}

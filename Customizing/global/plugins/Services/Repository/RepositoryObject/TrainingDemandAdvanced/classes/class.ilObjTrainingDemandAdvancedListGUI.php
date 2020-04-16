<?php
include_once("./Services/Repository/classes/class.ilObjectPluginListGUI.php");

/**
 * List gui class for plugin object in repository
 */
class ilObjTrainingDemandAdvancedListGUI extends ilObjectPluginListGUI
{
    /**
     * Init the type of the plugin. Same value as choosen in plugin.php
     */
    public function initType()
    {
        $this->setType("xtda");
    }

    /**
     * Get name of gui class handling the commands
     */
    public function getGuiClass()
    {
        return "ilObjTrainingDemandAdvancedGUI";
    }

    /**
     * Get commands
     */
    public function initCommands()
    {
        return array(array("permission" => "read",
                            "cmd" => "showContent",
                            "default" => true
                        ),
                        array("permission" => "write",
                            "cmd" => "editProperties",
                            "txt" => $this->txt("edit"),
                            "default" => false
                        )
                );
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

    public function getProperties()
    {
        $props = array();

        $this->plugin->includeClass("class.ilObjTrainingDemandAdvancedAccess.php");
        if (!ilObjTrainingDemandAdvancedAccess::_isOnline($this->obj_id)) {
            $props[] = array("alert" => true, "property" => $this->lng->txt("status"),
            "value" => $this->lng->txt("offline"));
        }

        return $props;
    }
}

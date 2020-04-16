<?php
include_once("./Services/Repository/classes/class.ilObjectPluginListGUI.php");

/**
 * List gui class for plugin object in repository
 */
class ilObjWebinarListGUI extends ilObjectPluginListGUI
{
    /**
     * Init the type of the plugin. Same value as choosen in plugin.php
     */
    public function initType()
    {
        $this->setType("xwbr");
    }

    /**
     * Get name of gui class handling the commands
     */
    public function getGuiClass()
    {
        return "ilObjWebinarGUI";
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

    /**
    * Get item properties
    *
    * @return	array		array of property arrays:
    *						"alert" (boolean) => display as an alert property (usually in red)
    *						"property" (string) => property name
    *						"value" (string) => property value
    */
    public function getProperties()
    {
        $props = parent::getProperties();

        require_once(__DIR__ . "/class.ilObjWebinarAccess.php");
        if (ilObjWebinarAccess::_isOffline($this->obj_id)) {
            $object = ilObjectFactory::getInstanceByObjId($this->obj_id);
            $showRegistrationInfo = false;
            $props[] = array("alert" => true, "property" => $object->pluginTxt("online_status"),
                "value" => $object->pluginTxt("webinar_offline"));
        }

        return $props;
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
}

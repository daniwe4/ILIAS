<?php
include_once("./Services/Repository/classes/class.ilObjectPluginListGUI.php");

/**
 * List gui class for plugin object in repository
 */
class ilObjAgendaItemPoolListGUI extends ilObjectPluginListGUI
{
    /**
     * Init the type of the plugin. Same value as choosen in plugin.php
     */
    public function initType()
    {
        $this->setType("xaip");
    }

    /**
     * Get name of gui class handling the commands
     */
    public function getGuiClass()
    {
        return "ilObjAgendaItemPoolGUI";
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

    /**
    * @inheritdoc
    */
    public function insertDeleteCommand()
    {
        $obj = ilObjectFactory::getInstanceByObjId($this->obj_id);

        if (!$obj->areItemsUsed()) {
            parent::insertDeleteCommand();
        }
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
        global $lng;

        $props = parent::getProperties();

        include_once __DIR__ . '/class.ilObjAgendaItemPoolAccess.php';
        if (ilObjAgendaItemPoolAccess::_isOffline($this->obj_id)) {
            $props[] = array("alert" => true, "property" => $lng->txt("status"),
                "value" => $lng->txt("offline"));
        }
        return $props;
    }
}

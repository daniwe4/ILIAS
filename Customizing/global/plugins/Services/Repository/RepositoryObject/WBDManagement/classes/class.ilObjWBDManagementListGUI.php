<?php
include_once("./Services/Repository/classes/class.ilObjectPluginListGUI.php");

/**
 * List gui class for plugin object in repository.
 *
 * @author
 * @copyright Extended GPL, see LICENSE
 */
class ilObjWBDManagementListGUI extends ilObjectPluginListGUI
{
    /**
     * Init the type of the plugin. Same value as choosen in plugin.php.
     *
     * @return 	void
     */
    public function initType()
    {
        $this->setType("xwbm");
    }

    public function getGuiClass() : string
    {
        return "ilObjWBDManagementGUI";
    }

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
     * @inheritDoc
     */
    public function getProperties()
    {
        $props = parent::getProperties();

        require_once(__DIR__ . "/class.ilObjWBDManagementAccess.php");
        if (ilObjWBDManagementAccess::_isOffline($this->obj_id)) {
            /** @var ilObjWBDManagement $object */
            $object = ilObjectFactory::getInstanceByObjId($this->obj_id);
            $txt = $object->txtClosure();
            $props[] = array("alert" => true, "property" => $txt("online_status"),
                "value" => $txt("offline"));
        }

        return $props;
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

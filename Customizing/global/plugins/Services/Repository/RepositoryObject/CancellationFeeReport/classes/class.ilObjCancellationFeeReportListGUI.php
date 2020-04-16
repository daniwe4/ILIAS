<?php
include_once("./Services/Repository/classes/class.ilObjectPluginListGUI.php");

/**
 * List gui class for plugin object in repository.
 *
 * @author
 * @copyright Extended GPL, see LICENSE
 */
class ilObjCancellationFeeReportListGUI extends ilObjectPluginListGUI
{
    /**
     * Init the type of the plugin. Same value as choosen in plugin.php.
     *
     * @return 	void
     */
    public function initType()
    {
        $this->setType("xcfr");
    }

    /**
     * Get name of gui class handling the commands.
     *
     * @return 	ilObjCancellationFeeReportGUI
     */
    public function getGuiClass()
    {
        return "ilObjCancellationFeeReportGUI";
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
        require_once(__DIR__ . "/class.ilObjCancellationFeeReportAccess.php");
        if (ilObjCancellationFeeReportAccess::_isOffline($this->obj_id)) {
            $object = ilObjectFactory::getInstanceByObjId($this->obj_id);
            $props[] = array("alert" => true, "property" => $object->pluginTxt("online_status"),
                "value" => $object->pluginTxt("offline"));
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

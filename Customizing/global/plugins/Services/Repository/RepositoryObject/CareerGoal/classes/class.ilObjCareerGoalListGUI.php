<?php
include_once "./Services/Repository/classes/class.ilObjectPluginListGUI.php";

/**
 * ListGUI implementation for career goal object plugin. This one
 * handles the presentation in container items (categories, courses, ...)
 * together with the corresponfing ...Access class.
 *
 * @author 		Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilObjCareerGoalListGUI extends ilObjectPluginListGUI
{
	public function initType()
	{
		$this->setType("xcgo");
	}

	/**
	 * Get name of gui class handling the commands
	 */
	public function getGuiClass()
	{
		return "ilObjCareerGoalGUI";
	}

	/**
	 * Get commands
	 */
	public function initCommands()
	{
		return array(
				array("permission" => "read",
					  "cmd" => "showContent",
					  "default" => true)
			  , array("permission" => "write",
					  "cmd" => "editProperties",
					  "txt" => $this->txt("edit"),
					  "default" => false)
				);
	}

	/**
	 * Get item properties
	 *
	 * @return 	array 	array of property arrays:
	 *					"alert" (boolean) => display as an alert property (usually in red)
	 *					"property" (string) => property name
	 *					"value" (string) => property value
	 */
	public function getProperties()
	{
		global $lng, $ilUser;

		$props = array();

		$this->plugin->includeClass("class.ilObjCareerGoalAccess.php");
		if (\ilObjCareerGoalAccess::_isOffline($this->obj_id)) {
			$props[] = array("alert" => true, "property" => $this->txt("status"),
			"value" => $this->txt("offline"));
		}

		return $props;
	}
}

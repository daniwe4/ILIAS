<?php
include_once "./Services/Repository/classes/class.ilObjectPluginListGUI.php";

/**
 * ListGUI implementation for talent assessment object plugin. This one
 * handles the presentation in container items (categories, courses, ...)
 * together with the corresponfing ...Access class.
 *
 * @author 		Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilObjTalentAssessmentReportListGUI extends ilObjectPluginListGUI
{
	public function initType()
	{
		$this->setType("xtar");
	}

	/**
	 * Get name of gui class handling the commands
	 */
	public function getGuiClass()
	{
		return "ilObjTalentAssessmentReportGUI";
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
	 * @return	array		array of property arrays:
	 *						"alert" (boolean) => display as an alert property (usually in red)
	 *						"property" (string) => property name
	 *						"value" (string) => property value
	 */
	public function getProperties()
	{
		global $lng;

		$props = parent::getProperties();

		$this->plugin->includeClass("class.ilObjTalentAssessmentReportAccess.php");
		if(ilObjTalentAssessmentReportAccess::_isOffline($this->obj_id))
		{
			$props[] = array("alert" => true, "property" => $lng->txt("status"),
				"value" => $lng->txt("offline"));
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

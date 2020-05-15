<?php

require_once 'Services/Repository/classes/class.ilObjectPluginListGUI.php';

/**
* ListGUI implementation for Report object plugin. This one
* handles the presentation in container items (categories, courses, ...)
* together with the corresponfing  Report Access class.
*/
class ilObjEnhancedLPReportListGUI extends ilObjectPluginListGUI
{
	/**
	* This is probably more of a hack, since this functions responsibility nothing has to do with GUI properties, as it would seem.
	*/
	public function initType()
	{
		$this->setType("xlpr");
		$this->timings_enabled = false;
	}

	/**
	* Get commands
	*/
	public function initCommands()
	{
		return array(
			array(
				"permission" => "read",
				"cmd" => "showContent",
				"txt" => "show",
				"default" => true),
			array(
				"permission" => "write",
				"cmd" => "settings",
				"txt" => $this->lng->txt("edit"),
				"default" => false)
		);
	}

	/**
	* Get name of gui class handling the commands
	*/
	public function getGuiClass()
	{
		return "ilObjEnhancedLPReportGUI";
	}

	public function getProperties()
	{
		$props = array();

		$this->plugin->includeClass("class.ilObjEnhancedLPReportAccess.php");
		if (ilObjEnhancedLPReportAccess::_isOffline($this->obj_id)) {
			$props[] = array("alert" => true, "property" => $this->lng->txt("status"),
			"value" => $this->lng->txt("offline"));
		}
		return $props;
	}
}

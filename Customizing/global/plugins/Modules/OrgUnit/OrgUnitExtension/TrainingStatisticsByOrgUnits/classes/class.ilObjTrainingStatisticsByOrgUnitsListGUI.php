<?php

declare(strict_types=1);

include_once("./Services/Repository/classes/class.ilObjectPluginListGUI.php");

/**
 * List gui class for plugin object in repository.
 */
class ilObjTrainingStatisticsByOrgUnitsListGUI extends ilOrgUnitExtensionListGUI
{
    /**
     * Init the type of the plugin. Same value as choosen in plugin.php.
     *
     * @return 	void
     */
    public function initType()
    {
        $this->setType("xtou");
    }

    /**
     * Get name of gui class handling the commands.
     *
     * @return 	string
     */
    public function getGuiClass()
    {
        return "ilObjTrainingStatisticsByOrgUnitsGUI";
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
        $this->copy_enabled = false;
    }

    /**
     * Get commands
     */
    public function initCommands()
    {
        $this->getPlugin()->includeClass("class.ilObjTrainingStatisticsByOrgUnitsGUI.php");
        return array(
            array(
                "permission" => "write",
                "cmd" => ilObjTrainingStatisticsByOrgUnitsGUI::CMD_EDIT_SETTINGS,
                "txt" => $this->txt("edit"),
                "default" => false
            ),
            array(
                "permission" => "read",
                "cmd" => ilObjTrainingStatisticsByOrgUnitsGUI::CMD_SHOW_REPORT,
                "default" => true
            )
        );
    }

    public function getProperties()
    {
        $props = array();

        $this->getPlugin()->includeClass("class.ilObjTrainingStatisticsByOrgUnitsAccess.php");
        if (ilObjTrainingStatisticsByOrgUnitsAccess::_isOffline($this->obj_id)) {
            $props[] = array("alert" => true, "property" => $this->lng->txt("status"),
                "value" => $this->lng->txt("offline"));
        }

        return $props;
    }

    public function getCommandLink($a_cmd)
    {
        if ($a_cmd == ilObjTrainingStatisticsByOrgUnitsGUI::CMD_SHOW_REPORT) {
            return $this->getPlugin()->getSingleViewReportLink();
        }

        return parent::getCommandLink($a_cmd);
    }
}

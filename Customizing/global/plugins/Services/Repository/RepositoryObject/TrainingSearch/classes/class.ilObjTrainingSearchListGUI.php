<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

require_once "./Services/Repository/classes/class.ilObjectPluginListGUI.php";

/**
 * List gui class for plugin object in repository
 */
class ilObjTrainingSearchListGUI extends ilObjectPluginListGUI
{
    /**
     * @inheritdoc
     */
    public function initType()
    {
        $this->setType("xtrs");
    }

    /**
     * @inheritdoc
     */
    public function getGuiClass()
    {
        return "ilObjTrainingSearchGUI";
    }

    /**
     * @inheritdoc
     */
    public function initCommands()
    {
        return array(
            array(
                "permission" => "read",
                "cmd" => "showContent",
                "default" => true
            ),
            array(
                "permission" => "write",
                "cmd" => "editProperties",
                "txt" => $this->lng->txt("edit"),
                "default" => false
            )
        );
    }

    /**
     * @inheritdoc
     */
    protected function initListActions()
    {
        $this->copy_enabled = true;
        parent::initListActions();
    }

    /**
     * @inheritdoc
     */
    public function getProperties()
    {
        $props = parent::getProperties();

        $this->plugin->includeClass("class.ilObjTrainingSearchAccess.php");
        if (ilObjTrainingSearchAccess::_isOffline($this->obj_id)) {
            $props[] = array("alert" => true, "property" => $this->lng->txt("status"),
            "value" => $this->lng->txt("offline"));
        }

        return $props;
    }
}

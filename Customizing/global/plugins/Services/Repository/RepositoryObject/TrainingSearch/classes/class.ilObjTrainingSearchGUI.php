<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

require_once "Services/Repository/classes/class.ilObjectPluginGUI.php";
require_once "Services/Form/classes/class.ilPropertyFormGUI.php";
require_once __DIR__ . "/Settings/class.ilTrainingSearchSettingsGUI.php";
require_once __DIR__ . "/Search/class.ilCoursesGUI.php";

/**
 * @ilCtrl_isCalledBy ilObjTrainingSearchGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI, ilTrainingSearchGUI
 * @ilCtrl_Calls ilObjTrainingSearchGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjTrainingSearchGUI: ilTrainingSearchSettingsGUI, ilCoursesGUI, ilTrainingSearchPageGUI
 */

class ilObjTrainingSearchGUI extends ilObjectPluginGUI
{
    const CMD_SHOW_CONTENT = "showContent";

    const TAB_SETTINGS = "tabSettings";
    const TAB_COURSES = "tabCourses";

    /**
     * Called after parent constructor. It's possible to define some plugin special values
     */
    protected function afterConstructor()
    {
        global $DIC;

        $this->g_tabs = $DIC["ilTabs"];
        $this->g_access = $DIC["ilAccess"];
        $this->g_ctrl = $DIC["ilCtrl"];
        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC["lng"];
    }

    /**
    * Get type.  Same value as choosen in plugin.php
    */
    final public function getType()
    {
        return "xtrs";
    }

    /**
    * @inheritdoc
    */
    public function performCommand($cmd)
    {
        $next_class = $this->g_ctrl->getNextClass();
        switch ($next_class) {
            case "iltrainingsearchsettingsgui":
                $this->activateTab(self::TAB_SETTINGS);
                $this->setSubTabs("settings");
                $gui = $this->object->getDI()["settings.gui"];
                $this->g_ctrl->forwardCommand($gui);
                break;
            case "iltrainingsearchpagegui":
                $this->g_tabs->clearTargets();
                $xpage_id = ilContainer::_lookupContainerSetting(
                    $this->object->getId(),
                    "xhtml_page"
                );
                if ($xpage_id > 0) {
                    $this->g_tabs->setBackTarget(
                        $this->lng->txt("cntr_back_to_old_editor"),
                        $this->g_ctrl->getLinkTarget($this, "switchToOldEditor"),
                        "_top"
                    );
                } else {
                    $link = $this->g_ctrl->getLinkTargetByClass(
                        "iltrainingsearchsettingsgui",
                        ilTrainingSearchSettingsGUI::CMD_EDIT_PROPERTIES
                    );
                    $this->g_tabs->setBackTarget($this->lng->txt("back"), $link);
                }

                $gui = $this->object->getDI()["search.pageedit"];
                $this->g_ctrl->forwardCommand($gui);
                break;
            case "ilcoursesgui":
                $this->activateTab(self::TAB_COURSES);
                $read = $this->g_access->checkAccess("read", "", $this->object->getRefId());
                $write = $this->g_access->checkAccess("write", "", $this->object->getRefId());
                $use = $this->g_access->checkAccess("use_search", "", $this->object->getRefId());

                if (!$write) {
                    $this->tpl->setTitle("");
                }

                if ($read && !$write && !$use) {
                    $this->redirectInfoTab();
                }

                $gui = $this->object->getDI()["search.gui"];
                $this->g_ctrl->forwardCommand($gui);
                break;
            default:
                switch ($cmd) {
                    case ilTrainingSearchSettingsGUI::CMD_EDIT_PROPERTIES:
                    case self::CMD_SHOW_CONTENT:
                        if ($this->g_access->checkAccess("write", "", $this->object->getRefId())) {
                            $this->redirectSearch();
                        } elseif ($this->g_access->checkAccess("use_search", "", $this->object->getRefId())) {
                            $this->redirectSearch();
                        } else {
                            $this->redirectInfoTab();
                        }
                        break;
                    default:
                        throw new Exception("Unknown command: " . $cmd);
                }
        }
    }

    /**
    * Add object to locator
    */
    public function addLocatorItems()
    {
        $create_mode = $this->getCreationMode();
        $read = $this->g_access->checkAccess("read", "", $this->object->getRefId());
        $write = $this->g_access->checkAccess("write", "", $this->object->getRefId());
        $use = $this->g_access->checkAccess("use_search", "", $this->object->getRefId());

        if (!$create_mode &&
            ($write || ($read && !$use))
        ) {
            $this->locator->addItem(
                $this->object->getTitle(),
                $this->ctrl->getLinkTarget($this, $this->getStandardCmd()),
                "",
                $_GET["ref_id"]
            );
        } else {
            $this->locator->clearItems();
        }
    }

    /**
    * @inheritdoc
    */
    public function getAfterCreationCmd()
    {
        return ilTrainingSearchSettingsGUI::CMD_EDIT_PROPERTIES;
    }

    /**
    * @inheritdoc
    */
    public function getStandardCmd()
    {
        return self::CMD_SHOW_CONTENT;
    }

    protected function redirectInfoTab()
    {
        $link = $this->g_ctrl->getLinkTargetByClass(
            array("ilObjTrainingSearchGUI", "ilInfoScreenGUI"),
            "showSummary",
            "",
            false,
            false
        );
        \ilUtil::redirect($link);
    }

    protected function redirectSettings()
    {
        $link = $this->g_ctrl->getLinkTargetByClass(
            array("ilTrainingSearchSettingsGUI"),
            ilTrainingSearchSettingsGUI::CMD_EDIT_PROPERTIES,
            "",
            false,
            false
        );
        \ilUtil::redirect($link);
    }

    protected function redirectSearch()
    {
        $link = $this->g_ctrl->getLinkTargetByClass(
            array("ilCoursesGUI"),
            ilCoursesGUI::CMD_SHOW,
            "",
            false,
            false
        );
        \ilUtil::redirect($link);
    }

    public function setTabs()
    {
        $read = $this->g_access->checkAccess("read", "", $this->object->getRefId());
        $write = $this->g_access->checkAccess("write", "", $this->object->getRefId());
        $use = $this->g_access->checkAccess("use_search", "", $this->object->getRefId());

        if ($write || ($read && !$write && !$use)) {
            $this->addInfoTab();
        }

        if ($write) {
            $link = $this->g_ctrl->getLinkTargetByClass(
                array("ilTrainingSearchSettingsGUI"),
                ilTrainingSearchSettingsGUI::CMD_EDIT_PROPERTIES
            );

            $this->tabs_gui->addTab(self::TAB_SETTINGS, $this->txt(self::TAB_SETTINGS), $link);

            $link = $this->g_ctrl->getLinkTargetByClass(
                array("ilCoursesGUI"),
                ilCoursesGUI::CMD_SHOW
            );

            $this->tabs_gui->addTab(self::TAB_COURSES, $this->txt(self::TAB_COURSES), $link);
        }

        $this->addPermissionTab();
    }

    protected function activateTab($tab)
    {
        $this->g_tabs->activateTab($tab);
    }

    protected function setSubTabs($subtab)
    {
        $link = $this->g_ctrl->getLinkTargetByClass(
            array("ilTrainingSearchPageGUI"),
            "edit"
        );

        $this->tabs_gui->addSubTab("page", $this->txt("page"), $link);

        $link = $this->g_ctrl->getLinkTargetByClass(
            array("ilTrainingSearchSettingsGUI"),
            ilTrainingSearchSettingsGUI::CMD_EDIT_PROPERTIES
        );

        $this->tabs_gui->addSubTab("settings", $this->txt(self::TAB_SETTINGS), $link);

        $this->tabs_gui->activateSubTab($subtab);
    }

    public static function _goto($a_target)
    {
        $ref_id = (int) $a_target[0];

        $script = self::getForwardScript($_GET, $ref_id);
        ilUtil::redirect($script);
    }

    protected static function getForwardScript(array $get, $ref_id)
    {
        global $DIC;
        $ctrl = $DIC["ilCtrl"];
        $ctrl->setTargetScript("ilias.php");
        $ctrl->initBaseClass("ilobjplugindispatchgui");
        foreach ($get as $key => $val) {
            if (!in_array($key, ilCoursesGUI::$alllowed_params)) {
                continue;
            }

            if ($val == -1) {
                continue;
            }

            if ($key == ilCoursesGUI::F_DURATION) {
                $ctrl->setParameterByClass("ilCoursesGUI", ilCoursesGUI::F_DURATION_START, $val["start"]);
                $ctrl->setParameterByClass("ilCoursesGUI", ilCoursesGUI::F_DURATION_END, $val["end"]);
            } elseif ($key === "cmd" && is_array($get["cmd"])) {
                $cmd = array_shift(array_keys($get["cmd"]));
                $ctrl->setParameterByClass("ilCoursesGUI", $key, $cmd);
            } else {
                $ctrl->setParameterByClass("ilCoursesGUI", $key, $val);
            }
        }

        if (!in_array("cmd", $get) &&
            in_array(ilCoursesGUI::$alllowed_params, $get)
        ) {
            $ctrl->setParameterByClass("ilCoursesGUI", "cmd", "filter");
        }

        $ctrl->setParameterByClass("ilCoursesGUI", "ref_id", $ref_id);

        $link = $ctrl->getLinkTargetByClass(array("ilobjplugindispatchgui", "ilObjTrainingSearchGUI", "ilCoursesGUI"), "", "", false, false);
        $ctrl->clearParametersByClass("ilCoursesGUI");

        return $link;
    }
}

<?php
include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once(__DIR__ . "/Settings/class.ilCourseClassificationGUI.php");

/**
 * Plugin object GUI class. Baseclass for all GUI action in ILIAS
 *
 * @ilCtrl_isCalledBy ilObjCourseClassificationGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjCourseClassificationGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjCourseClassificationGUI: ilCourseClassificationGUI, ilExportGUI
 */
class ilObjCourseClassificationGUI extends ilObjectPluginGUI
{
    /**
     * Property of parent gui
     *
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * Called after parent constructor. It's possible to define some plugin special values
     */
    protected function afterConstructor()
    {
        global $DIC;

        $this->g_tabs = $DIC->tabs();
        $this->g_access = $DIC->access();
    }

    /**
    * Get type.  Same value as choosen in plugin.php
    */
    final public function getType()
    {
        return "xccl";
    }

    /**
    * Handles all commmands of this class, centralizes permission checks
    */
    public function performCommand($cmd)
    {
        $next_class = $this->ctrl->getNextClass();
        $this->activateTab($cmd);
        switch ($next_class) {
            case "ilcourseclassificationgui":
                $gui = new ilCourseClassificationGUI($this, $this->object->getActions());
                $this->ctrl->forwardCommand($gui);
                break;
            default:
                switch ($cmd) {
                    case ilCourseClassificationGUI::CMD_EDIT_PROPERTIES:
                        $this->redirectCourseClassification($cmd);
                        break;
                    case ilCourseClassificationGUI::CMD_SHOW_CONTENT:
                        $this->redirectInfoTab();
                        break;
                    default:
                        throw new Exception(__METHOD__ . " unknown command " . $cmd);
                }
        }
    }

    /**
    * After object has been created -> jump to this command
    */
    public function getAfterCreationCmd()
    {
        return ilCourseClassificationGUI::CMD_EDIT_PROPERTIES;
    }

    /**
    * Get standard command
    */
    public function getStandardCmd()
    {
        return ilCourseClassificationGUI::CMD_SHOW_CONTENT;
    }

    /**
     * Redirect via link to course classification gui
     *
     * @param string 	$cmd
     *
     * @return null
     */
    protected function redirectCourseClassification($cmd)
    {
        $link = $this->ctrl->getLinkTargetByClass(
            array("ilObjCourseClassificationGUI", "ilCourseClassificationGUI"),
            $cmd,
            "",
            false,
            false
        );
        \ilUtil::redirect($link);
    }

    /**
     * Redirect via link to Info tab
     *
     * @return null
     */
    protected function redirectInfoTab()
    {
        $link = $this->ctrl->getLinkTargetByClass(
            array("ilObjCourseClassificationGUI", "ilInfoScreenGUI"),
            "showSummary",
            "",
            false,
            false
        );
        \ilUtil::redirect($link);
    }

    /**
     * Set the tabs for the site and activate current
     *
     * @param string 	$cmd
     *
     * @return null
     */
    protected function setTabs()
    {
        $this->addInfoTab();
        $settings = $this->ctrl->getLinkTargetByClass(array("ilObjCourseClassificationGUI", "ilCourseClassificationGUI"), ilCourseClassificationGUI::CMD_EDIT_PROPERTIES);

        if ($this->g_access->checkAccess("write", "", $this->object->getRefId())) {
            $this->g_tabs->addTab(ilCourseClassificationGUI::CMD_EDIT_PROPERTIES, $this->txt("tab_settings"), $settings);
        }

        $this->addExportTab();

        $this->addPermissionTab();
    }

    /**
    * @inheritdoc
    */
    public function addInfoItems($info)
    {
        $course_classification = $this->object->getCourseClassification();
        $actions = $this->object->getActions();

        $content = (string) $course_classification->getContent();
        if ($content == "") {
            $content = "-";
        }
        $goals = (string) $course_classification->getGoals();
        if ($goals == "") {
            $goals = "-";
        }
        $preparation = (string) $course_classification->getPreparation();
        if ($preparation == "") {
            $preparation = "-";
        }
        $target_group_description = (string) $course_classification->getTargetGroupDescription();
        if ($target_group_description == "") {
            $target_group_description = "-";
        }

        $additional_links = $course_classification->getAdditionalLinks();
        $additional_links_text = "";
        if (count($additional_links) > 0) {
            $tpl = new ilTemplate("tpl.info_additional_links.html", true, true, $this->getPlugin()->getDirectory());
            foreach ($additional_links as $additional_link) {
                $tpl->setCurrentBlock("entry");
                $tpl->setVariable("URL", $additional_link->getUrl());
                $tpl->setVariable("LABEL", $additional_link->getLabel());
                $tpl->parseCurrentBlock();
            }
            $additional_links_text = $tpl->get();
        }

        $info->addSection($this->txt("informations"));
        $info->addProperty($this->txt("type"), implode("<br>", $actions->getTypeName($course_classification->getType())));
        $info->addProperty($this->txt("edu_program"), implode("<br>", $actions->getEduProgramName($course_classification->getEduProgram())));
        $info->addProperty($this->txt("categories"), implode("<br>", $actions->getCategoryNames($course_classification->getCategories())));
        $info->addProperty($this->txt("topics"), implode("<br>", $actions->getTopicsNames($course_classification->getTopics())));
        $info->addProperty($this->txt("content"), nl2br($content));
        $info->addProperty($this->txt("goals"), nl2br($goals));
        $info->addProperty($this->txt("preparation"), nl2br($preparation));
        $info->addProperty($this->txt("method"), implode("<br>", $actions->getMethodNames($course_classification->getMethod())));
        $info->addProperty($this->txt("media"), implode("<br>", $actions->getMediaNames($course_classification->getMedia())));
        $info->addProperty($this->txt("target_groups"), implode("<br>", $actions->getTargetGroupNames($course_classification->getTargetGroup())));
        $info->addProperty($this->txt("target_group_description"), nl2br($target_group_description));

        $contact = $course_classification->getContact();
        $name = $contact->getName();
        if ($name == "") {
            $name = "-";
        }

        $responsibility = $contact->getResponsibility();
        if ($responsibility == "") {
            $responsibility = "-";
        }
        $phone = $contact->getPhone();
        if ($phone == "") {
            $phone = "-";
        }
        $mail = $contact->getMail();
        if ($mail == "") {
            $mail = "-";
        }

        $info->addSection($this->txt("contact"));
        $info->addProperty($this->txt("name"), $name);
        $info->addProperty($this->txt("responsibility"), $responsibility);
        $info->addProperty($this->txt("phone"), $phone);
        $info->addProperty($this->txt("mail"), $mail);
        $info->addSection($this->txt("additional_links"));
        $info->addProperty("", $additional_links_text);
    }

    /**
     * activate current tab
     *
     * @param string 	$cmd
     *
     * @return null
     */
    protected function activateTab($cmd)
    {
        $this->g_tabs->activateTab($cmd);
    }
}

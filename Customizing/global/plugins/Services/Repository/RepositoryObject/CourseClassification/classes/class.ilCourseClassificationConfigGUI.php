<?php
include_once("./Services/Component/classes/class.ilPluginConfigGUI.php");
use CaT\Plugins\CourseClassification;

/**
 * Config gui to define auto admin exeutions
 *
 * @ilCtrl_Calls ilCourseClassificationConfigGUI: ilOptionsGUI, ilCategoriesGUI, ilTopicGUI, ilTypeGUI, ilEduprogramGUI
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilCourseClassificationConfigGUI extends ilPluginConfigGUI
{
    const CMD_CONFIGURE = "configure";

    const OPTION_TOPIC = "Topic";
    const OPTION_CATEGORY = "Category";
    const OPTION_EDU_PROGRAM = "Eduprogram";
    const OPTION_MEDIA = "Media";
    const OPTION_METHOD = "Method";
    const OPTION_TARGET_GROUP = "TargetGroup";
    const OPTION_TYPE = "Type";

    /**
     * @var \$ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var \$ilTabs
     */
    protected $g_tabs;

    public function __construct()
    {
        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->g_tabs = $DIC->tabs();
    }

    /**
     * @inheritdoc
     *
     * @throws \Exception
     */
    public function performCommand($cmd)
    {
        require_once($this->plugin_object->getDirectory() . "/classes/Options/class.ilOptionsGUI.php");
        require_once($this->plugin_object->getDirectory() . "/classes/Options/Category/class.ilCategoriesGUI.php");
        require_once($this->plugin_object->getDirectory() . "/classes/Options/Topic/class.ilTopicGUI.php");
        require_once($this->plugin_object->getDirectory() . "/classes/Options/Type/class.ilTypeGUI.php");
        require_once($this->plugin_object->getDirectory() . "/classes/Options/Eduprogram/class.ilEduprogramGUI.php");

        $type = $this->getOptionType();
        $actions = $this->getActionsByType($type);
        $this->setTabs($type);

        $next_class = $this->g_ctrl->getNextClass();

        switch ($next_class) {
            case "iltopicgui":
                $backend = new CourseClassification\Options\Topic\TopicBackend($actions);
                $table_processor = new CourseClassification\TableProcessing\TableProcessor($backend);
                $gui = new ilTopicGUI($this, $actions, $table_processor, $type);
                $this->g_ctrl->forwardCommand($gui);
                break;
            case "ilcategoriesgui":
                $backend = new CourseClassification\Options\OptionBackend($actions);
                $table_processor = new CourseClassification\TableProcessing\TableProcessor($backend);
                $gui = new ilCategoriesGUI($this, $actions, $table_processor, $type);
                $this->g_ctrl->forwardCommand($gui);
                break;
            case "iloptionsgui":
                $backend = new CourseClassification\Options\OptionBackend($actions);
                $table_processor = new CourseClassification\TableProcessing\TableProcessor($backend);
                $gui = new ilOptionsGUI($this, $actions, $table_processor, $type);
                $this->g_ctrl->forwardCommand($gui);
                break;
            case "iltypegui":
                $backend = new CourseClassification\Options\OptionBackend($actions);
                $table_processor = new CourseClassification\TableProcessing\TableProcessor($backend);
                $gui = new ilTypeGUI($this, $actions, $table_processor, $type);
                $this->g_ctrl->forwardCommand($gui);
                break;
            case "ileduprogramgui":
                $backend = new CourseClassification\Options\OptionBackend($actions);
                $table_processor = new CourseClassification\TableProcessing\TableProcessor($backend);
                $gui = new ilEduprogramGUI($this, $actions, $table_processor, $type);
                $this->g_ctrl->forwardCommand($gui);
                break;
            default:
                switch ($cmd) {
                    case self::CMD_CONFIGURE:
                        $cmd = ilOptionsGUI::CMD_SHOW_OPTIONS;
                        //Just change command before Forwarding
                        // no break
                    case ilOptionsGUI::CMD_SHOW_OPTIONS:
                        $this->forwardOptions($cmd, $type);
                        break;
                }
        }
    }

    /**
     * Redirect to options gui. To keep the next class oportunity
     *
     * @param string 	$cmd
     * @param string 	$type
     *
     * @return null
     */
    protected function forwardOptions($cmd, $type)
    {
        $this->g_ctrl->setParameterByClass("ilOptionsGUI", "type", $type);
        switch ($type) {
            case self::OPTION_TOPIC:
                $link = $this->g_ctrl->getLinkTargetByClass(array("ilCourseClassificationConfigGUI", "ilTopicGUI"), $cmd, '', false, false);
                break;
            case self::OPTION_CATEGORY:
                $link = $this->g_ctrl->getLinkTargetByClass(array("ilCourseClassificationConfigGUI", "ilCategoriesGUI"), $cmd, '', false, false);
                break;
            case self::OPTION_TYPE:
                $link = $this->g_ctrl->getLinkTargetByClass(array("ilCourseClassificationConfigGUI", "ilTypeGUI"), $cmd, '', false, false);
                break;
            case self::OPTION_EDU_PROGRAM:
                $link = $this->g_ctrl->getLinkTargetByClass(array("ilCourseClassificationConfigGUI", "ilEduprogramGUI"), $cmd, '', false, false);
                break;
            default:
                $link = $this->g_ctrl->getLinkTargetByClass(array("ilCourseClassificationConfigGUI", "ilOptionsGUI"), $cmd, '', false, false);
        }
        $this->g_ctrl->setParameterByClass("ilOptionsGUI", "type", null);

        ilUtil::redirect($link);
    }

    /**
     * Sets tabs for provider, trainer and tags
     *
     * @param string 	$type
     */
    protected function setTabs($type)
    {
        $this->g_ctrl->setParameterByClass("ilCategoriesGUI", "type", self::OPTION_CATEGORY);
        $link_category = $this->g_ctrl->getLinkTargetByClass(array("ilCourseClassificationConfigGUI", "ilCategoriesGUI"), ilOptionsGUI::CMD_SHOW_OPTIONS);
        $tabs[$this->plugin_object->txt("conf_options_category")] = array(self::OPTION_CATEGORY, $link_category);
        $this->g_ctrl->setParameterByClass("ilCategoriesGUI", "type", null);

        $this->g_ctrl->setParameterByClass("ilEduprogramGUI", "type", self::OPTION_EDU_PROGRAM);
        $link_edu_program = $this->g_ctrl->getLinkTargetByClass(array("ilCourseClassificationConfigGUI", "ilEduprogramGUI"), ilOptionsGUI::CMD_SHOW_OPTIONS);
        $tabs[$this->plugin_object->txt("conf_options_edu_programme")] = array(self::OPTION_EDU_PROGRAM, $link_edu_program);
        $this->g_ctrl->setParameterByClass("ilEduprogramGUI", "type", null);

        $this->g_ctrl->setParameterByClass("ilOptionsGUI", "type", self::OPTION_MEDIA);
        $link_media = $this->g_ctrl->getLinkTargetByClass(array("ilCourseClassificationConfigGUI", "ilOptionsGUI"), ilOptionsGUI::CMD_SHOW_OPTIONS);
        $tabs[$this->plugin_object->txt("conf_options_media")] = array(self::OPTION_MEDIA, $link_media);
        $this->g_ctrl->setParameterByClass("ilOptionsGUI", "type", null);

        $this->g_ctrl->setParameterByClass("ilOptionsGUI", "type", self::OPTION_METHOD);
        $link_method = $this->g_ctrl->getLinkTargetByClass(array("ilCourseClassificationConfigGUI", "ilOptionsGUI"), ilOptionsGUI::CMD_SHOW_OPTIONS);
        $tabs[$this->plugin_object->txt("conf_options_method")] = array(self::OPTION_METHOD, $link_method);
        $this->g_ctrl->setParameterByClass("ilOptionsGUI", "type", null);

        $this->g_ctrl->setParameterByClass("ilOptionsGUI", "type", self::OPTION_TARGET_GROUP);
        $link_target_group = $this->g_ctrl->getLinkTargetByClass(array("ilCourseClassificationConfigGUI", "ilOptionsGUI"), ilOptionsGUI::CMD_SHOW_OPTIONS);
        $tabs[$this->plugin_object->txt("conf_options_target_group")] = array(self::OPTION_TARGET_GROUP, $link_target_group);
        $this->g_ctrl->setParameterByClass("ilOptionsGUI", "type", null);

        $this->g_ctrl->setParameterByClass("ilTopicGUI", "type", self::OPTION_TOPIC);
        $link_topic = $this->g_ctrl->getLinkTargetByClass(array("ilCourseClassificationConfigGUI", "ilTopicGUI"), ilOptionsGUI::CMD_SHOW_OPTIONS);
        $tabs[$this->plugin_object->txt("conf_options_topic")] = array(self::OPTION_TOPIC, $link_topic);
        $this->g_ctrl->setParameterByClass("ilTopicGUI", "type", null);

        $this->g_ctrl->setParameterByClass("ilTypeGUI", "type", self::OPTION_TYPE);
        $link_type = $this->g_ctrl->getLinkTargetByClass(array("ilCourseClassificationConfigGUI", "ilTypeGUI"), ilOptionsGUI::CMD_SHOW_OPTIONS);
        $tabs[$this->plugin_object->txt("conf_options_type")] = array(self::OPTION_TYPE, $link_type);
        $this->g_ctrl->setParameterByClass("ilTypeGUI", "type", null);

        ksort($tabs);

        foreach ($tabs as $caption => $tab) {
            $this->g_tabs->addTab($tab[0], $caption, $tab[1]);
        }

        $this->g_tabs->activateTab($type);
    }

    /**
     * Get actions by type of option
     *
     * @param string 	$type
     *
     * @return ilActions
     */
    protected function getActionsByType($type)
    {
        return $this->plugin_object->getActionsByType($type);
    }

    /**
     * Get type of current option
     *
     * @return string
     */
    protected function getOptionType()
    {
        if (isset($_GET["type"]) && $_GET["type"] != "") {
            return $_GET["type"];
        }

        return self::OPTION_CATEGORY;
    }
}

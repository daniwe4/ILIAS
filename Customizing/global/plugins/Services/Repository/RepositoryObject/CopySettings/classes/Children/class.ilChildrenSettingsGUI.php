<?php

use CaT\Plugins\CopySettings;

/**
 * This gui shows the copy informations for each child in container
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilChildrenSettingsGUI
{
    use CopySettings\Helper;

    const CMD_SHOW_SETTINGS = "showSettings";
    const CMD_SAVE_SETTINGS = "saveSettings";

    const F_COPY_OPTIONS = "cp_options";

    protected static $reference_types = array("crsr", "catr");

    /**
     * @var CopySettings\ilObjectActions
     */
    protected $actions;

    /**
     * @var ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $g_tpl;

    public function __construct(CopySettings\ilObjectActions $actions, \Closure $txt)
    {
        $this->actions = $actions;
        $this->txt = $txt;

        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->g_tpl->addJavaScript('./Services/CopyWizard/js/ilContainer.js');
        $this->g_tpl->setVariable('BODY_ATTRIBUTES', 'onload="ilDisableChilds(\'cmd\');"');
    }

    public function executeCommand()
    {
        $next_class = $this->g_ctrl->getNextClass();
        $cmd = $this->g_ctrl->getCmd();

        switch ($next_class) {
            default:
                switch ($cmd) {
                    case self::CMD_SHOW_SETTINGS:
                        $this->showSettings();
                        break;
                    case self::CMD_SAVE_SETTINGS:
                        $this->saveSettings();
                        break;
                    default:
                        throw new Exception("Unknown command: " . $cmd);
                }
        }
    }

    /**
     * Display current copy settings for children
     *
     * @return void
     */
    protected function showSettings()
    {
        $parent_container = $this->actions->getParentContainer();

        if ($parent_container) {
            $table = new CopySettings\Children\ilChildrenSettingsTableGUI($this, self::CMD_SHOW_SETTINGS, $this->actions, $this->txt);
            $table->parseSource($parent_container->getRefId());

            $table->setTitle($this->txt("copy_settings"));
            $table->clearCommandButtons();
            $table->addCommandButton(self::CMD_SAVE_SETTINGS, $this->txt("save"));
            $table->addCommandButton(self::CMD_SHOW_SETTINGS, $this->txt("cancel"));

            $this->g_tpl->setContent($table->getHTML());
        } else {
            ilUtil::sendInfo($this->txt("not_below_course"));
        }
    }

    /**
     * Save new copy settings for children
     *
     * @return void
     */
    protected function saveSettings()
    {
        $post = $_POST;
        $cp_options = $post[self::F_COPY_OPTIONS];

        $this->actions->clearCopySettings();

        if (isset($cp_options) && count($cp_options) != 0) {
            foreach ($cp_options as $ref_id => $process_type) {
                $obj_id = ilObject::_lookupObjId((int) $ref_id);
                $type = ilObject::_lookupType($obj_id);
                $this->actions->createCopySettings($ref_id, $obj_id, in_array($type, self::$reference_types), $process_type["type"]);
            }
        }

        ilUtil::sendSuccess($this->txt("copy_settings_successful_saved"), true);
        $this->g_ctrl->redirect($this, self::CMD_SHOW_SETTINGS);
    }
}

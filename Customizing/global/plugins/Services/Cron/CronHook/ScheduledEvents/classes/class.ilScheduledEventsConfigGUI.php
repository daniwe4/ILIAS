<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Component/classes/class.ilPluginConfigGUI.php");
include_once("class.ilScheduleTableGUI.php");

/**
 * Configuration gui class of plugin.
 * Just forwarding to sub configuration classes.
 *
 * @ilCtrl_Calls ilScheduledEventsConfigGUI: ilScheduledEventsGUI
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilScheduledEventsConfigGUI extends ilPluginConfigGUI
{
    const STD_CONFIG_CMD = "configure";
    const TAB_SCHEDULE_EVENTS = "scheduled_events";

    /**
    * @var ilCtrl
    */
    protected $g_ctrl;

    /**
     * @var ilTabsGUI
     */
    protected $g_tabs;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $g_tpl;

    public function __construct()
    {
        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->g_tabs = $DIC->tabs();
        $this->g_tpl = $DIC->ui()->mainTemplate();
    }

    /**
     * @inheritdoc
     */
    public function performCommand($cmd)
    {
        $next_class = $this->g_ctrl->getNextClass();
        $this->setTabs();
        switch ($cmd) {
            case self::STD_CONFIG_CMD:
            case self::TAB_SCHEDULE_EVENTS:
                $this->showQueue();
                break;
            default:
                throw new Exception(__METHOD__ . ":: Unknown command: " . $cmd);
        }
    }

    /**
     * Sets tabs.
     *
     * @return void
     */
    protected function setTabs()
    {
        $this->g_tabs->addTab(
            self::TAB_SCHEDULE_EVENTS,
            $this->plugin_object->txt(self::TAB_SCHEDULE_EVENTS),
            $this->g_ctrl->getLinkTarget($this, self::TAB_SCHEDULE_EVENTS)
        );
    }

    /**
     * Show scheduled (future) events.
     *
     * @return null
     */
    protected function showQueue()
    {
        $actions = $this->plugin_object->getActions();
        $plug_dir = $this->plugin_object->getDirectory();
        $data = $actions->getAllEvents();

        $table = new ilScheduleTableGUI($this, $this->plugin_object->txtClosure(), $plug_dir);
        $table->setData($data);
        $this->g_tpl->setContent($table->getHtml());
    }
}

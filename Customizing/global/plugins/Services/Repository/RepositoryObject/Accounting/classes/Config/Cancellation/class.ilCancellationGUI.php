<?php

declare(strict_types=1);

/**
 * @ilCtrl_Calls ilCancellationGUI: ilScaleGUI, ilCancellationRolesGUI, ilCancellationParticipantStatusGUI
 */
class ilCancellationGUI
{
    const TAB_SCALES = "tab_scales";
    const TAB_ROLES = "tab_roles";
    const TAB_STATES = "tab_states";

    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilTabsGUI
     */
    protected $tabs;
    /**
     * @var Closure
     */
    protected $txt;
    /**
     * @var string
     */
    protected $scale_gui_link;
    /**
     * @var ilScaleGUI
     */
    protected $scale_gui;
    /**
     * @var string
     */
    protected $roles_gui_link;
    /**
     * @var ilCancellationRolesGUI
     */
    protected $roles_gui;
    /**
     * @var string
     */
    protected $states_gui_link;
    /**
     * @var ilCancellationParticipantStatusGUI
     */
    protected $states_gui;

    public function __construct(
        ilCtrl $ctrl,
        ilTabsGUI $tabs,
        Closure $txt,
        string $scale_gui_link,
        ilScaleGUI $scale_gui,
        string $roles_gui_link,
        ilCancellationRolesGUI $roles_gui,
        string $states_gui_link,
        ilCancellationParticipantStatusGUI $states_gui
    ) {
        $this->ctrl = $ctrl;
        $this->tabs = $tabs;
        $this->txt = $txt;
        $this->scale_gui_link = $scale_gui_link;
        $this->scale_gui = $scale_gui;
        $this->roles_gui_link = $roles_gui_link;
        $this->roles_gui = $roles_gui;
        $this->states_gui_link = $states_gui_link;
        $this->states_gui = $states_gui;
    }

    /**
     * @throws ilCtrlException
     * @throws Exception
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass();
        $this->setSubTabs();
        switch ($next_class) {
            case "ilscalegui":
                $this->tabs->activateSubTab(self::TAB_SCALES);
                $this->ctrl->forwardCommand($this->scale_gui);
                break;
            case "ilcancellationrolesgui":
                $this->tabs->activateSubTab(self::TAB_ROLES);
                $this->ctrl->forwardCommand($this->roles_gui);
                break;
            case "ilcancellationparticipantstatusgui":
                $this->tabs->activateSubTab(self::TAB_STATES);
                $this->ctrl->forwardCommand($this->states_gui);
                break;

            default:
                throw new Exception("Unknown next class: " . $next_class);
        }
    }

    protected function setSubTabs()
    {
        $this->tabs->addSubTab(
            self::TAB_SCALES,
            $this->txt(self::TAB_SCALES),
            $this->scale_gui_link
        );

        $this->tabs->addSubTab(
            self::TAB_ROLES,
            $this->txt(self::TAB_ROLES),
            $this->roles_gui_link
        );

        $this->tabs->addSubTab(
            self::TAB_STATES,
            $this->txt(self::TAB_STATES),
            $this->states_gui_link
        );
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}

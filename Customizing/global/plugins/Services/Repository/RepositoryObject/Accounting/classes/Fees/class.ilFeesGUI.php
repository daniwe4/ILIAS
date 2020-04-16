<?php

declare(strict_types=1);

use CaT\Plugins\Accounting\DI;

/**
 * @ilCtrl_Calls ilFeesGUI: ilFeeGUI
 * @ilCtrl_Calls ilFeesGUI: ilCancellationFeeGUI
 */
class ilFeesGUI
{
    const CMD_SHOW_FEE_SETTINGS = "showFeeSettings";
    const CMD_SAVE_FEE_SETTINGS = "saveFeeSettings";
    const CMD_SHOW_CANCELLATION_FEE_SETTINGS = "showCancellationFeeSettings";
    const CMD_SAVE_CANCELLATION_FEE_SETTINGS = "saveCancellationFeeSettings";

    const TAB_FEE = "fee";
    const TAB_CANCELLATION_FEE = "cancellation_fee";

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
    protected $cancel_link;
    /**
     * @var ilCancellationFeeGUI
     */
    protected $cancel_gui;
    /**
     * @var string
     */
    protected $fee_link;
    /**
     * @var ilFeeGUI
     */
    protected $fee_gui;

    public function __construct(
        ilCtrl $ctrl,
        ilTabsGUI $tabs,
        Closure $txt,
        string $cancel_link,
        \ilCancellationFeeGUI $cancel_gui,
        string $fee_link,
        \ilFeeGUI $fee_gui
    ) {
        $this->ctrl = $ctrl;
        $this->tabs = $tabs;
        $this->txt = $txt;
        $this->cancel_link = $cancel_link;
        $this->cancel_gui = $cancel_gui;
        $this->fee_link = $fee_link;
        $this->fee_gui = $fee_gui;
    }

    /**
     * @inheritdoc
     * @throws Exception if cmd is not knwown
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass();

        $this->setSubTabs();
        switch ($next_class) {
            case "ilfeegui":
                $this->tabs->activateSubTab(self::TAB_FEE);
                $this->ctrl->forwardCommand($this->fee_gui);
                break;
            case "ilcancellationfeegui":
                $this->tabs->activateSubTab(self::TAB_CANCELLATION_FEE);
                $this->ctrl->forwardCommand($this->cancel_gui);
                break;
            default:
                throw new Exception("Unknown next class: " . $next_class);
        }
    }

    protected function setSubTabs()
    {
        $this->tabs->addSubTab(
            self::TAB_FEE,
            $this->txt(self::TAB_FEE),
            $this->fee_link
        );

        $this->tabs->addSubTab(
            self::TAB_CANCELLATION_FEE,
            $this->txt(self::TAB_CANCELLATION_FEE),
            $this->cancel_link
        );
    }

    public function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}

<?php

declare(strict_types=1);

use \CaT\Plugins\Accounting\DI;

require_once __DIR__ . "/../vendor/autoload.php";

/**
 * Config gui to define auto admin exeutions
 *
 * @ilCtrl_Calls ilAccountingConfigGUI: ilVatRateGUI, ilCostTypeGUI, ilCancellationGUI
 *
 * @author Daniel Weise 	<daniel.weise@concepts-and-training.de>
 */
class ilAccountingConfigGUI extends ilPluginConfigGUI
{
    use DI;

    const TAB_COST_TYPE = "costtype";
    const TAB_VAT_RATE = "vatrate";
    const TAB_CANCELLATION = "cancellation";
    const CMD_CONFIGURE = "configure";

    /**
     * @var DI
     */
    protected $dic;

    /**
     * @param $cmd
     * @throws Exception
     */
    public function performCommand($cmd)
    {
        global $DIC;
        $this->dic = $this->getPluginDIC($this->plugin_object, $DIC);
        $this->setTabs();
        $next_class = $this->dic["ilCtrl"]->getNextClass();
        switch ($next_class) {
            case 'ilcosttypegui':
                $this->dic["ilTabs"]->activateTab(self::TAB_COST_TYPE);
                $this->dic["ilCtrl"]->forwardCommand($this->dic["config.costtype.gui"]);
                break;
            case 'ilvatrategui':
                $this->dic["ilTabs"]->activateTab(self::TAB_VAT_RATE);
                $this->dic["ilCtrl"]->forwardCommand($this->dic["config.vatrate.gui"]);
                break;
            case "ilcancellationgui":
                $this->dic["ilTabs"]->activateTab(self::TAB_CANCELLATION);
                $this->dic["ilCtrl"]->forwardCommand($this->dic["config.cancellation.gui"]);
                break;
            default:
                switch ($cmd) {
                    case self::CMD_CONFIGURE:
                        $this->redirectCostTypes();
                        break;
                    default:
                        throw new Exception("Unknown command: " . $cmd);
                }
        }
    }

    protected function redirectCostTypes()
    {
        ilUtil::redirect($this->dic["config.costtype.gui.link"]);
    }

    protected function setTabs()
    {
        $this->dic["ilTabs"]->addTab(
            self::TAB_COST_TYPE,
            $this->dic["txtclosure"](self::TAB_COST_TYPE),
            $this->dic["config.costtype.gui.link"]
        );

        $this->dic["ilTabs"]->addTab(
            self::TAB_VAT_RATE,
            $this->dic["txtclosure"](self::TAB_VAT_RATE),
            $this->dic["config.vatrate.gui.link"]
        );

        $this->dic["ilTabs"]->addTab(
            self::TAB_CANCELLATION,
            $this->dic["txtclosure"](self::TAB_CANCELLATION),
            $this->dic["config.cancellation.scale.gui.link"]
        );
    }
}

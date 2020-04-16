<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

use \CaT\Plugins\Agenda\DI;

require_once __DIR__ . "/../vendor/autoload.php";

/**
 * Config gui to define auto admin exeutions
 *
 * @ilCtrl_Calls ilAgendaConfigGUI: ilBlocksGUI
 */
class ilAgendaConfigGUI extends ilPluginConfigGUI
{
    use DI;

    const CMD_CONFIGURE = "configure";

    const TAB_BLOCK = "tab_block";

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
        $this->setTabs();
        $next_class = $this->getDic()["ilCtrl"]->getNextClass();
        switch ($next_class) {
            case 'ilblocksgui':
                $this->getDic()["ilTabs"]->activateTab(self::TAB_BLOCK);
                $this->getDic()["ilCtrl"]->forwardCommand($this->getDic()["config.blocks.gui"]);
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
        ilUtil::redirect($this->getDic()["config.blocks.gui.link"]);
    }

    protected function setTabs()
    {
        $this->getDic()["ilTabs"]->addTab(
            self::TAB_BLOCK,
            $this->getDic()["txtclosure"](self::TAB_BLOCK),
            $this->getDic()["config.blocks.gui.link"]
        );
    }

    protected function getDic() : Pimple\Container
    {
        if (is_null($this->dic)) {
            global $DIC;
            $this->dic = $this->getPluginDIC($this->plugin_object, $DIC);
        }
        return $this->dic;
    }
}

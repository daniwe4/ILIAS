<?php

declare(strict_types=1);

use CaT\Plugins\TrainingStatisticsByOrgUnits\DI;

/**
 * Plugin base class. Keeps all information the plugin needs.
 */
class ilTrainingStatisticsByOrgUnitsPlugin extends ilOrgUnitExtensionPlugin
{
    use DI;

    /**
     * @var ilPluginActions
     */
    protected $plugin_actions;

    /**
     * @inheritDoc
     */
    public function getPluginName()
    {
        return "TrainingStatisticsByOrgUnits";
    }

    /**
     * Defines custom uninstall action like delete table or something else.
     *
     * @return 	void
     */
    protected function uninstallCustom()
    {
    }

    public function getSingleViewReportLink() : string
    {
        return $this->getDic()["report.singleview.gui.link"];
    }

    protected function getDic() : Pimple\Container
    {
        global $DIC;
        return $this->getPluginDIC($this, $DIC);
    }
}

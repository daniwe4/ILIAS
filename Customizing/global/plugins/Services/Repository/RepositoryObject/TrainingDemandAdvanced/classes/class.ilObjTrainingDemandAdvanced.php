<?php
include_once("Services/Repository/classes/class.ilObjectPlugin.php");
require_once 'Services/TMS/ReportUtilities/classes/class.ilTreeObjectDiscovery.php';

use \CaT\Plugins\TrainingDemandAdvanced as TDA;

/**
 * Object of the plugin
 */
class ilObjTrainingDemandAdvanced extends ilObjectPlugin
{

    /**
     * @var int
     */
    protected $current_usr_id;

    public function __construct($a_ref_id = 0)
    {
        parent::__construct($a_ref_id = 0);

        global $DIC;
        $this->db = $DIC['ilDB'];
        $this->g_tree = $DIC['tree'];
        $this->settings_repository = new TDA\Settings\DBSettingsRepository($this->db);
        $this->current_usr_id = (int) $DIC["ilUser"]->getId();
    }

    /**
     * Init the type of the plugin. Same value as choosen in plugin.php
     */
    public function initType()
    {
        $this->setType("xtda");
    }


    public function doCreate()
    {
        $this->settings = $this->settings_repository->create((int) $this->getId());
    }

    /**
     * Get called if the object get be updated
     * Update additoinal setting values
     */
    public function doUpdate()
    {
        $this->settings_repository->update($this->settings);
    }

    /**
     * Get called after object creation to read further information
     */
    public function doRead()
    {
        $this->settings = $this->settings_repository->load((int) $this->getId());
    }

    /**
     * Get called if the object should be deleted.
     * Delete additional settings
     */
    public function doDelete()
    {
        $this->delete($this->settings);
    }

    /**
     * Get called if the object get be coppied.
     * Copy additional settings to new object
     */
    public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
    {
        $new_obj->settings = $new_obj->settings->withGlobal($this->settings->isGlobal());
        $new_obj->update();
    }


    public function settings()
    {
        return $this->settings;
    }

    public function setSettings(TDA\Settings\Settings $set)
    {
        $this->settings = $set;
    }

    public function report()
    {
        return new TDA\Report(
            $this->plugin,
            $this->db,
            new TDA\ActionLinksHelper(),
            new ilTreeObjectDiscovery($this->g_tree),
            $this->settings,
            $this,
            $this->current_usr_id
        );
    }
}

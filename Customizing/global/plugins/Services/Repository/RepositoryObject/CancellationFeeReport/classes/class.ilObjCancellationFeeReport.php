<?php

use CaT\Plugins\CancellationFeeReport\Settings as Settings;

/**
 * Object of the plugin.
 *
 * @author
 * @copyright Extended GPL, see LICENSE
 */
class ilObjCancellationFeeReport extends ilObjectPlugin
{
    protected $settings_repository;
    protected $settings;


    public function __construct($ref_id = 0)
    {
        parent::__construct($ref_id);
        $this->settings_repository = ilCancellationFeeReportPlugin::dic()['Settings.SettingsRepository'];
    }

    /**
     * Get called if the object get be coppied.
     * Copy additional settings to new object.
     *
     * @return 	void
     */
    public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
    {
        $new_obj->setSettings(
            $new_obj->getSettings()
                ->withOnline(false)
                ->withGlobal(
                    $this->getSettings()->isGlobal()
                )
        );
    }

    /**
     * Create settings or proider objects.
     *
     * @return 	void
     */
    public function doCreate()
    {
        $this->settings = $this->settings_repository->create($this->getId());
    }

    /**
     * Get called if the object should be deleted.
     * Delete additional settings.
     *
     * @return 	void
     */
    public function doDelete()
    {
        $this->settings_repository->delete($this->settings_repository->load($this->getId()));
    }

    /**
     * Get called after object creation to read further information.
     *
     * @return 	void
     */
    public function doRead()
    {
        $this->settings = $this->settings_repository->load($this->getId());
    }

    /**
     * Get called if the object get be updated.
     * Update additoinal setting values.
     *
     * @return 	void
     */
    public function doUpdate()
    {
        $this->settings_repository->update($this->settings);
    }

    /**
     * Get settings.
     *
     * @return 	void
     */
    public function getSettings() : Settings\Settings
    {
        return $this->settings;
    }

    public function setSettings(Settings\Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Init the type of the plugin. Same value as choosen in plugin.php
     */
    public function initType()
    {
        $this->setType("xcfr");
    }

    public function pluginTxt(string $code)
    {
        return $this->getPlugin()->txt($code);
    }
}

<?php
include_once("Services/Repository/classes/class.ilObjectPlugin.php");

use CaT\Plugins\EmployeeBookingOverview as EBO;

/**
 * Object of the plugin.
 *
 * @author
 * @copyright Extended GPL, see LICENSE
 */
class ilObjEmployeeBookingOverview extends ilObjectPlugin
{
    use EBO\DI;

    /**
     * @var EBO\Settings\Settings
     */
    protected $settings;

    /**
     * @var EBO\Settings\SettingsRepository
     */
    protected $settings_db;

    /**
     * @var \Pimple\Container
     */
    protected $dic;

    /**
     * Get called if the object get be coppied.
     * Copy additional settings to new object.
     *
     * @return 	void
     */
    public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
    {
        $n_settings = $new_obj->getSettings();
        $settings = $this->getSettings();
        $n_settings = $n_settings->withGlobal($settings->isGlobal())
            ->withOnline($settings->isOnline())
            ->withInvisibleCourseTopics($settings->getInvisibleCourseTopics())
        ;
        $new_obj->setSettings($n_settings);
        $new_obj->update();
    }

    /**
     * Create settings or proider objects.
     *
     * @return 	void
     */
    public function doCreate()
    {
        $this->settings = $this->getSettingsDB()->create((int) $this->getId());
    }

    /**
     * Get called if the object should be deleted.
     * Delete additional settings.
     *
     * @return 	void
     */
    public function doDelete()
    {
        $this->delete($this->settings);
    }

    /**
     * Get called after object creation to read further information.
     *
     * @return 	void
     */
    public function doRead()
    {
        $this->settings = $this->getSettingsDB()->load((int) $this->getId());
    }

    /**
     * Get called if the object get be updated.
     * Update additoinal setting values.
     *
     * @return 	void
     */
    public function doUpdate()
    {
        $this->getSettingsDB()->update($this->settings);
    }

    /**
     * Get settings.
     */
    public function getSettings() : EBO\Settings\Settings
    {
        return $this->settings;
    }

    /**
     * Init the type of the plugin. Same value as choosen in plugin.php
     */
    public function initType()
    {
        $this->setType("xebo");
    }

    public function setSettings(EBO\Settings\Settings $set)
    {
        $this->settings = $set;
    }

    protected function getSettingsDB() : EBO\Settings\SettingsRepository
    {
        if (is_null($this->settings_db)) {
            $this->settings_db = $this->getDic()["settings.gui.db"];
        }
        return $this->settings_db;
    }

    public function txtClosure() : Closure
    {
        return function ($code) {
            return $this->plugin->txt($code);
        };
    }

    public function getPluginDir() : string
    {
        return $this->plugin->getDirectory();
    }

    protected function getDIC() : \Pimple\Container
    {
        if (is_null($this->dic)) {
            global $DIC;
            $this->dic = $this->getObjectDIC($this, $DIC);
        }

        return $this->dic;
    }
}

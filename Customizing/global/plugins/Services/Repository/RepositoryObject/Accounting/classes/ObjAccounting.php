<?php
namespace CaT\Plugins\Accounting;

/**
 * Interface for the pluginobject to make it more testable
 *
 * @author Daniel Weise 	<daniel.weise@concepts-and-training.de>
 */
interface ObjAccounting
{
    /**
     * Get actions for this object
     *
     * @return \CaT\Plugins\Accounting\ilObjectActions
     */
    public function getObjectActions();

    /**
     * Get a data db object
     */
    public function getAccountingDataDB();

    /**
     * Get a settings db object
     */
    public function getAccountingSettingsDB();

    /**
     * Init the type of the plugin. Same value as choosen in plugin.php
     */
    public function initType();

    /**
     * Get called if the object get be updated
     * Update additoinal setting values
     */
    public function doUpdate();

    /**
     * Get called after object creation to read further information
     */
    public function doRead();

    /**
     * Get called if the object should be deleted.
     * Delete additional settings
     */
    public function doDelete();

    /**
     * Update the settings
     */
    public function updateSettings(\Closure $update);

    /**
     * Set settings
     *
     * @param Accounting\Settings\Settings 	$xacc 	a settings object
     */
    public function setSettings(Settings\Settings $xacc);

    /**
     * Get settings object
     *
     * @return Accounting\Settings\Settings
     */
    public function getSettings();

    /**
     * Get object of parent course
     *
     * @return \ilObjCourse
     */
    public function getParentCourse();
}

<?php
use CaT\Plugins\TalentAssessment;

include_once("./Services/Repository/classes/class.ilObjectPlugin.php");

/**
 * Application class for talent assessment repository object.
 *
 * @author 		Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilObjTalentAssessment extends ilObjectPlugin implements TalentAssessment\ObjTalentAssessment
{
	const PLUGIN_TYPE = "xtas";

	/**
	 * @var TalentAssessment|null
	 */
	protected $settings;

	/**
	 * @var ilActions|null
	 */
	protected $actions;

	public function __construct($a_ref_id = 0)
	{
		$this->settings = null;
		$this->actions = null;

		parent::__construct($a_ref_id);

		// read settings again from the database if we already
		// have a ref id, as 0 for ref_id means, we are just
		// creating the object.
		if ($a_ref_id !== 0) {
			$this->doRead();
		}
	}

	/**
	 * @return ilActions
	 */
	public function getActions()
	{
		if ($this->actions === null) {
			$this->actions = new TalentAssessment\ilActions($this, $this->getSettingsDB(), $this->getObserverDB(), $this->getObservationsDB());
		}
		return $this->actions;
	}

	/**
	 * Get type.
	 */
	final public function initType()
	{
		$this->setType(self::PLUGIN_TYPE);
	}

	/**
	 * Create object
	 */
	public function doCreate()
	{
	}

	/**
	 * Read data from db
	 */
	public function doRead()
	{
		if ($this->plugin) {
			$this->settings = $this->getSettingsDB()->select((int)$this->getId());
		}
	}

	/**
	 * Update data
	 */
	public function doUpdate()
	{
		$this->getSettingsDB()->update($this->settings);
	}

	/**
	 * Delete data from db
	 */
	public function doDelete()
	{
		$this->getSettingsDB()->delete((int)$this->getId());
		$this->getObservationsDB()->deleteByTalentAssessmentId($this->getId());
	}

	/**
	 * Do Cloning
	 */
	public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
	{
		$talent_assessment = $this->getSettingsDB()->cloneTalentAssessment($new_obj->getId(), $this->getSettings());
		$new_obj->setSettings($talent_assessment);

		$title = $this->getActions()->getLocalRoleNameFor($new_obj->getId());
		$description = $this->getActions()->getLocalRoleDescriptionFor($new_obj->getId());

		$new_obj->getActions()->createLocalRole($new_obj, $title, $description);
	}

	// Custom stuffe
	/**
	 * @throws	LogicExxeption if object already has settings.
	 * @return	null
	 */
	public function setSettings(TalentAssessment\Settings\TalentAssessment $settings)
	{
		if ($this->settings !== null) {
			throw new \LogicException("Object already initialized.");
		}
		$this->settings = $settings;
	}

	/**
	 * @param	Closure	$update		function from Settings/WBD to Settings/WBD
	 * @return	null
	 */
	public function updateSettings(\Closure $update)
	{
		$this->settings = $update($this->getSettings());
	}

	/**
	 * @throws	LogicException if object is not properly initialized.
	 * @return	Settings\WBD
	 */
	public function getSettings()
	{
		if ($this->settings === null) {
			throw new \LogicException("Object not properly initialized.");
		}
		return $this->settings;
	}

	/**
	 * @return	$DB
	 */
	public function getSettingsDB()
	{
		return $this->plugin->getSettingsDB();
	}

	/**
	 * @return	$DB
	 */
	public function getObserverDB()
	{
		return $this->plugin->getObserverDB();
	}

	/**
	 * @return	$DB
	 */
	public function getObservationsDB()
	{
		return $this->plugin->getObservationsDB();
	}
}

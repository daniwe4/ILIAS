<?php
use CaT\Plugins\TalentAssessmentReport;

include_once("./Services/Repository/classes/class.ilObjectPlugin.php");

/**
 * Application class for talent assessment repository object.
 *
 * @author 		Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilObjTalentAssessmentReport extends ilObjectPlugin implements TalentAssessmentReport\ObjTalentAssessmentReport
{
	const PLUGIN_TYPE = "xtar";

	/**
	 * @var TalentAssessmentReport|null
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
			$this->actions = new TalentAssessmentReport\ilActions($this, $this->getSettingsDB(), $this->getReportDB());
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
		$settings = $this->getSettingsDB()->create((int)$this->getId(), false, false);
		$this->setSettings($settings);
	}

	/**
	 * Read data from db
	 */
	public function doRead()
	{
		if ($this->plugin) {
			$settings = $this->getSettingsDB()->selectFor((int)$this->getId());
			$this->setSettings($settings);
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
		$this->getSettingsDB()->deleteFor((int)$this->getId());
	}

	/**
	 * Do Cloning
	 */
	public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
	{
	}

	// Custom stuff
	/**
	 * @throws	LogicExxeption if object already has settings.
	 * @return	null
	 */
	public function setSettings(TalentAssessmentReport\Settings\TalentAssessmentReport $settings)
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
	 * @return	\CaT\Plugins\TalentAssessmentReport\Settings\TalentAssessmentReport
	 */
	public function getSettings()
	{
		if ($this->settings === null) {
			throw new \LogicException("Object not properly initialized.");
		}
		return $this->settings;
	}

	/**
	 * @return	\CaT\Plugins\TalentAssessmentReport\Settings\DB
	 */
	public function getSettingsDB()
	{
		if ($this->settings_db === null) {
			global $DIC;
			$this->settings_db = new \CaT\Plugins\TalentAssessmentReport\Settings\ilDB($DIC->database());
		}
		return $this->settings_db;
	}

	/**
	 * @return	\CaT\Plugins\TalentAssessmentReport\Reposrt\DB
	 */
	public function getReportDB()
	{
		if ($this->report_db === null) {
			global $DIC;
			$this->report_db = new \CaT\Plugins\TalentAssessmentReport\Report\ilDB($DIC->database());
		}
		return $this->report_db;
	}
}

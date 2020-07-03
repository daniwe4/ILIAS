<?php
include_once("Services/Repository/classes/class.ilObjectPlugin.php");
include_once("Services/Tracking/interfaces/interface.ilLPStatusPlugin.php");
include_once("Services/Tracking/classes/class.ilLPStatus.php");

require_once(__DIR__ . "/UnboundProvider.php");

use CaT\Plugins\OnlineSeminar;
use \CaT\Libs\ExcelWrapper;
use CaT\Ente\ILIAS\ilProviderObjectHelper;
use CaT\Plugins\OnlineSeminar\Config\Config;

/**
 * Object of the plugin
 */
class ilObjOnlineSeminar extends ilObjectPlugin implements ilLPStatusPluginInterface
{
    use ilProviderObjectHelper;
    use OnlineSeminar\DI;

    const F_URL = "f_url";
    const F_PHONE = "f_phone";
    const F_PIN = "f_pin";
    const F_REQUIRED_MINUTES = "f_required_minutes";
    const F_PASSWORD = "f_password";
    const F_TUTOR_LOGIN = "f_tutor_login";
    const F_TUTOR_PASSWORD = "tutor_password";

    /**
     * @var OnlineSeminar\VC\VCActions[]
     */
    protected $vc_actions = array();

    /**
     * @var OnlineSeminar\VC\DataImport[]
     */
    protected $vc_imports = array();

    /**
     * @var OnlineSeminar\VC\DataExport[]
     */
    protected $vc_exports = array();

    /**
     * @var OnlineSeminar\VC\FileStorage[]
     */
    protected $vc_storages = array();

    /**
     * @var OnlineSeminar\VC\FormHelper[]
     */
    protected $vc_form_helper = array();

    /**
     * Init the type of the plugin. Same value as choosen in plugin.php
     */
    public function initType()
    {
        $this->setType("xwbr");
    }

    public function doCreate()
    {
        $this->createUnboundProvider("crs", CaT\Plugins\OnlineSeminar\UnboundProvider::class, __DIR__ . "/UnboundProvider.php");
        $this->createUnboundProvider("crs", CaT\Plugins\OnlineSeminar\SharedUnboundProvider::class, __DIR__ . "/SharedUnboundProvider.php");
        $this->settings = $this->getSettingsDB()->create((int) $this->getId(), "");
    }

    /**
     * Get called if the object get be updated
     * Update additoinal setting values
     */
    public function doUpdate()
    {
        $this->getActions()->update($this->settings);
    }

    /**
     * Get called after object creation to read further information
     */
    public function doRead()
    {
        $this->settings = $this->getActions()->select();
    }

    /**
     * Get called if the object should be deleted.
     * Delete additional settings
     */
    public function doDelete()
    {
        $this->getVCActions()->delete();
        $this->getActions()->delete();
        $this->getFileStorage()->deleteCurrentFile();
        $this->deleteUnboundProviders();
    }

    /**
     * Get called if the object get be coppied.
     * Copy additional settings to new object
     */
    public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
    {
        $new_obj->doRead();

        $fnc = function ($s) {
            return $s
                ->withVCType($this->settings->getVCType())
                ->withBeginning($this->settings->getBeginning())
                ->withEnding($this->settings->getEnding())
                ->withAdmission($this->settings->getAdmission())
                ->withUrl($this->settings->getUrl())
                ->withLPMode($this->settings->getLPMode())
                ->withOnline(false);
        };

        $new_obj->updateSettings($fnc);
        $new_obj->getVCActions()->create();
        $specific_settings = $this->getVCActions()->select();
        $new_specific_settings = $new_obj->getVCActions()->select()
            ->withValuesOf($specific_settings);
        $new_obj->getVCActions()->update($new_specific_settings);

        $new_obj->update();
    }

    /**
     * Get parent course
     *
     * @return \ilObjCourse | null
     */
    public function getParentCourse()
    {
        global $DIC;
        $tree = $DIC->repositoryTree();

        $ref_id = $this->getRefId();
        if ($ref_id === null) {
            $ref_id = $_GET["ref_id"];
        }

        $parents = $tree->getPathFull($this->getRefId());
        $parent = array_filter($parents, function ($p) {
            if ($p["type"] == "crs") {
                return $p;
            }
        });

        if (count($parent) > 0) {
            $parent_crs = array_shift($parent);
            require_once("Services/Object/classes/class.ilObjectFactory.php");
            return ilObjectFactory::getInstanceByRefId($parent_crs["ref_id"]);
        }
        return null;
    }

    /**
     * Get ilActions
     *
     * @return ilActions
     */
    public function getActions()
    {
        if ($this->actions === null) {
            $this->actions = new OnlineSeminar\ilActions($this, $this->getSettingsDB(), $this->getParticipantsDB(), new OnlineSeminar\LPSettings\LPManagerImpl());
        }

        return $this->actions;
    }

    /**
     * Get action specific to vc
     *
     * @return VCActions
     */
    public function getVCActions()
    {
        $vc_type = $this->settings->getVCType();

        if (!array_key_exists($vc_type, $this->vc_actions)) {
            $db_string = "\\CaT\\Plugins\\OnlineSeminar\\VC\\" . $vc_type . "\\ilDB";
            $class_string = "\\CaT\\Plugins\\OnlineSeminar\\VC\\" . $vc_type . "\\ilActions";

            global $DIC;
            $db = new $db_string($DIC->database());
            $config = $this->getPluginDIC($this->plugin, $DIC)["config.db"];
            $this->vc_actions[$vc_type] = new $class_string($this, $db, $config);
        }

        return $this->vc_actions[$vc_type];
    }

    /**
     * Get the vc specific import object
     *
     * @return DataImport
     */
    public function getImport()
    {
        $vc_type = $this->settings->getVCType();

        if (!array_key_exists($vc_type, $this->vc_imports)) {
            $class_string = "\\CaT\\Plugins\\OnlineSeminar\\VC\\" . $vc_type . "\\Import";

            $this->vc_imports[$vc_type] = new $class_string(new OnlineSeminar\VC\PHPSpreadsheetParser());
        }

        return $this->vc_imports[$vc_type];
    }

    /**
     * Get the vc specific export object
     *
     * @return DataExport
     */
    public function getExport(ExcelWrapper\Writer $writer, ExcelWrapper\Spout\SpoutInterpreter $interpreter)
    {
        $vc_type = $this->settings->getVCType();

        if (!array_key_exists($vc_type, $this->vc_exports)) {
            $class_string = "\\CaT\\Plugins\\OnlineSeminar\\VC\\" . $vc_type . "\\Export";

            $this->vc_exports[$vc_type] = new $class_string($this->getVCActions(), $writer, $interpreter);
        }

        return $this->vc_exports[$vc_type];
    }

    /**
     * Get the vc specific storage object
     *
     * @return DataImport
     */
    public function getFileStorage()
    {
        $vc_type = $this->settings->getVCType();

        if (!array_key_exists($vc_type, $this->vc_storages)) {
            $class_string = "\\CaT\\Plugins\\OnlineSeminar\\VC\\" . $vc_type . "\\FileStorageImpl";

            $this->vc_storages[$vc_type] = new $class_string($this->getRefId());
        }

        return $this->vc_storages[$vc_type];
    }

    /**
     * Get the vc specific object for setting items
     *
     * @return DataImport
     */
    public function getVCFormHelper()
    {
        $vc_type = $this->settings->getVCType();

        if (!array_key_exists($vc_type, $this->vc_form_helper)) {
            $class_string = "\\CaT\\Plugins\\OnlineSeminar\\VC\\" . $vc_type . "\\FormHelperImpl";

            $this->vc_form_helper[$vc_type] = new $class_string($this, $this->getVCActions());
        }

        return $this->vc_form_helper[$vc_type];
    }

    /**
     * @inheritdoc
     */
    public function updateSettings(\Closure $update)
    {
        $this->settings = $update($this->getSettings());
    }

    /**
     * Get the settings
     *
     * @return OnlineSeminar\Settings\OnlineSeminar
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Set settings to object
     *
     * @param OnlineSeminar\Settings\OnlineSeminar
     */
    public function setSettings(OnlineSeminar\Settings\OnlineSeminar $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Get db of settings
     *
     * @return \CaT\Plugins\OnlineSeminar\Settings\DB
     */
    public function getSettingsDB()
    {
        if ($this->settings_db === null) {
            global $DIC;
            $db = $DIC->database();
            $this->settings_db = new \CaT\Plugins\OnlineSeminar\Settings\ilDB($db);
        }

        return $this->settings_db;
    }

    /**
     * Get db of participants
     *
     * @return \CaT\Plugins\OnlineSeminar\Participants\DB
     */
    public function getParticipantsDB()
    {
        if ($this->participants_db === null) {
            global $DIC;
            $db = $DIC->database();
            $this->participants_db = new \CaT\Plugins\OnlineSeminar\Participant\ilDB($db);
        }

        return $this->participants_db;
    }

    /**
     * @inheritdoc
     */
    public function getLPCompleted()
    {
        if (!$this->getSettings()->isFinished()) {
            return array();
        }

        $vc_settings = $this->getVCActions()->select();
        return $this->getParticipantsDB()->getCompletedUser($this->getId(), $vc_settings->getMinutesRequired());
    }

    /**
     * @inheritdoc
     */
    public function getLPNotAttempted()
    {
        return array();
    }

    /**
     * @inheritdoc
     */
    public function getLPFailed()
    {
        if (!$this->getSettings()->isFinished()) {
            return array();
        }

        $vc_settings = $this->getVCActions()->select();
        return $this->getParticipantsDB()->getFailedUser($this->getId(), $vc_settings->getMinutesRequired());
    }

    /**
     * @inheritdoc
     */
    public function getLPInProgress()
    {
        return array();
    }

    /**
     * @inheritdoc
     */
    public function getLPStatusForUser($a_user_id)
    {
        if (!$this->getSettings()->isFinished()) {
            return ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
        }

        $vc_settings = $this->getVCActions()->select();
        $lp_data = $this->getParticipantsDB()->getLPDataFor($this->getId(), $a_user_id);
        if ($lp_data["minutes"] === null && $lp_data["passed"] === null) {
            return ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
        }

        $status = $this->getLPManager()->coursePassed($vc_settings->getMinutesRequired(), $lp_data);

        if ($status) {
            return ilLPStatus::LP_STATUS_COMPLETED_NUM;
        }

        return ilLPStatus::LP_STATUS_FAILED_NUM;
    }

    /**
     * Get instance of lp manager
     *
     * @return OnlineSeminar\LPSettings\LPManager
     */
    protected function getLPManager()
    {
        if ($this->lp_manager === null) {
            $this->lp_manager = new OnlineSeminar\LPSettings\LPManagerImpl();
        }

        return $this->lp_manager;
    }

    /**
     * Translate code with plugin txt
     *
     * @param string 	$code
     *
     * @return Closure
     */
    public function pluginTxt(string $code)
    {
        return $this->getPlugin()->txt($code);
    }

    /**
     * Get plugin directory
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->getPlugin()->getDirectory();
    }

    /**
     * @inheritdoc
     */
    protected function getDIC()
    {
        return $GLOBALS["DIC"];
    }

    /**
     * Get the static link to enter the settings of online seminar
     *
     * @return string | null
     */
    public function getLinkToSettings()
    {
        require_once("Services/Link/classes/class.ilLink.php");
        global $DIC;
        $access = $DIC->access();

        $link = null;
        if ($access->checkAccess("visible", "", $this->getRefId())
            && $access->checkAccess("read", "", $this->getRefId())
            && $access->checkAccess("write", "", $this->getRefId())
        ) {
            $link = ilLink::_getLink($this->getRefId(), $this->getType(), array("cmd" => "editProperties"));
        }

        return $link;
    }

    /**
     * Closure to get txt from plugin
     *
     * @return Closure
     */
    public function txtClosure()
    {
        return function ($code) {
            return $this->txt($code);
        };
    }

    // for course creation
    /**
     * Will be called after course creation with configuration options.
     *
     * @param	mixed	$config
     * @return	void
     */
    public function afterCourseCreation($config)
    {
        $settings = $this->getSettings();

        if ($settings->getVCType() == "CSN") {
            $this->updateCSNSettings($settings, $config);
        } elseif ($settings->getVCType() == "Generic") {
            $this->updateGenericSettings($settings, $config);
        } else {
            throw new \RuntimeException("Unknown vc type");
        }

        $this->setSettings($settings);
        $this->doUpdate();
    }

    /**
     * Updates CSN settings with copy mechanism values
     *
     * @param OnlineSeminar\Settings\OnlineSeminar 	&$setings
     * @param mixed 	$config
     *
     * @return OnlineSeminar\Settings\OnlineSeminar
     */
    protected function updateCSNSettings(OnlineSeminar\Settings\OnlineSeminar &$settings, $config)
    {
        $vc_actions = $this->getVCActions();
        $vc_settings = $vc_actions->select();

        foreach ($config as $key => $value) {
            switch ($key) {
                case self::F_URL:
                    $settings = $settings->withUrl($value);
                    break;
                case self::F_PIN:
                    $vc_settings = $vc_settings->withPin($value);
                    break;
                case self::F_PHONE:
                    $vc_settings = $vc_settings->withPhone($value);
                    break;
                case self::F_REQUIRED_MINUTES:
                    $vc_settings = $vc_settings->withMinutesRequired((int) $value);
                    break;
                default:
                    throw new \RuntimeException("Can't process configuration '$key'");
            }
        }

        $vc_actions->update($vc_settings);
    }

    /**
     * Updates Generic settings with copy mechanism values
     *
     * @param OnlineSeminar\Settings\OnlineSeminar 	&$setings
     * @param mixed 	$config
     *
     * @return void
     */
    protected function updateGenericSettings(OnlineSeminar\Settings\OnlineSeminar &$settings, $config)
    {
        $vc_actions = $this->getVCActions();
        $vc_settings = $vc_actions->select();

        foreach ($config as $key => $value) {
            switch ($key) {
                case self::F_URL:
                    $settings = $settings->withUrl($value);
                    break;
                case self::F_PASSWORD:
                    $vc_settings = $vc_settings->withPassword($value);
                    break;
                case self::F_TUTOR_LOGIN:
                    $vc_settings = $vc_settings->withTutorLogin($value);
                    break;
                case self::F_TUTOR_PASSWORD:
                    $vc_settings = $vc_settings->withTutorPassword($value);
                    break;
                case self::F_REQUIRED_MINUTES:
                    $vc_settings = $vc_settings->withMinutesRequired((int) $value);
                    break;
                default:
                    throw new \RuntimeException("Can't process configuration '$key'");
            }
        }

        $vc_actions->update($vc_settings);
    }
}

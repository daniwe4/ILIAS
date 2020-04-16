<?php
include_once("Services/Repository/classes/class.ilObjectPlugin.php");
require_once(__DIR__ . "/UnboundProvider.php");
require_once(__DIR__ . "/Purposes/WBD/IliasWBDObjectProvider.php");
require_once(__DIR__ . "/Purposes/WBD/IliasWBDUserDataProvider.php");


use CaT\Ente\ILIAS\ilProviderObjectHelper;
use CaT\Plugins\EduTracking;
use CaT\Ente\ILIAS\ilHandlerObjectHelper;
use ILIAS\TMS\CourseInfo;
use ILIAS\TMS\CourseInfoHelper;
use CaT\Plugins\EduTracking\Purposes\WBD;

class ilObjEduTracking extends ilObjectPlugin
{
    use ilProviderObjectHelper;
    use ilHandlerObjectHelper;
    use CourseInfoHelper;

    public function __construct($a_ref_id = 0)
    {
        parent::__construct($a_ref_id);
    }

    public function initType()
    {
        $this->setType("xetr");
    }

    public function doCreate()
    {
        $this->createUnboundProvider("crs", CaT\Plugins\EduTracking\UnboundProvider::class, __DIR__ . "/UnboundProvider.php");
        $this->getDBFor("GTI")->create($this);
        $this->getDBFor("WBD")->create($this);
        $this->getDBFor("IDD")->create($this);
    }

    public function doRead()
    {
    }

    public function doUpdate()
    {
        $this->getDBFor("GTI")->selectFor($this)->update();
        $this->getDBFor("WBD")->selectFor($this)->update();
        $this->getDBFor("IDD")->selectFor($this)->update();
    }

    public function doDelete()
    {
        $this->deleteUnboundProviders();
        $this->getDBFor("GTI")->selectFor($this)->delete();
        $this->getDBFor("WBD")->selectFor($this)->delete();
        $this->getDBFor("IDD")->selectFor($this)->delete();
        return true;
    }

    public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
    {
        $settings = $this->getDBFor("GTI")->selectFor($this);
        $new_settings = $new_obj->getDBFor("GTI")->selectFor($new_obj);
        $new_settings = $new_settings
            ->withCategoryId($settings->getCategoryId())
            ->withSetTrainingTimeManually($settings->getSetTrainingTimeManually())
            ->withMinutes($settings->getMinutes())
        ;
        $new_settings->update();

        $settings = $this->getDBFor("WBD")->selectFor($this);
        $new_settings = $new_obj->getDBFor("WBD")->selectFor($new_obj);
        $new_settings = $new_settings
            ->withEducationType($settings->getEducationType())
            ->withEducationContent($settings->getEducationContent())
        ;
        $new_settings->update();


        $settings = $this->getDBFor("IDD")->selectFor($this);
        $new_settings = $new_obj->getDBFor("IDD")->selectFor($new_obj);
        $new_settings = $new_settings->withMinutes($settings->getMinutes());
        $new_settings->update();
    }

    /**
     * Get the object actions
     *
     * @return ilObjActions
     */
    public function getActions()
    {
        if ($this->actions === null) {
            $this->actions = new EduTracking\ilObjActions($this);
        }

        return $this->actions;
    }

    /**
     * Get config actions for type
     *
     * @var string 	$purpose_name
     *
     * @return ilActions
     */
    public function getActionsFor($purpose_name)
    {
        $purpose_name = strtoupper($purpose_name);
        switch ($purpose_name) {
            case "WBD":
                return new \CaT\Plugins\EduTracking\Purposes\WBD\ilActions($this, $this->getDBFor($purpose_name));
            case "IDD":
                global $DIC;
                return new \CaT\Plugins\EduTracking\Purposes\IDD\ilActions($this, $this->getDBFor($purpose_name));
            case "GTI":
                return new \CaT\Plugins\EduTracking\Purposes\GTI\ilActions(
                    $this,
                    $this->getDBFor($purpose_name),
                    $this->getPlugin()->getConfigActionsFor('GTI')
                );
            default:
                throw new Exception("unknown purpose type");
        }
    }

    /**
     * Get config actions for type
     *
     * @var string 	$purpose_name
     *
     * @return DB
     */
    public function getDBFor($purpose_name)
    {
        $purpose_name = strtoupper($purpose_name);
        global $DIC;
        switch ($purpose_name) {
            case "WBD":
                return new \CaT\Plugins\EduTracking\Purposes\WBD\ilDB($DIC->database(), $DIC['ilAppEventHandler']);
            case "IDD":
                return new \CaT\Plugins\EduTracking\Purposes\IDD\ilDB($DIC->database(), $DIC['ilAppEventHandler']);
            case "GTI":
                return new \CaT\Plugins\EduTracking\Purposes\GTI\ilDB($DIC->database(), $DIC['ilAppEventHandler']);
            default:
                throw new Exception("unknown purpose type");
        }
    }

    /**
     * Get the parent course
     *
     * @return ilObjCourse | null
     */
    public function getParentCourse()
    {
        $ref_id = $this->getRefId();
        if (is_null($ref_id)) {
            $ref_ids = ilObject::_getAllReferences($this->getId());
            $ref_id = array_shift($ref_ids);
        }

        global $DIC;
        $tree = $DIC->repositoryTree();
        $crs_obj = null;
        foreach ($tree->getPathFull($ref_id) as $hop) {
            if ($hop['type'] === 'crs') {
                require_once("Services/Object/classes/class.ilObjectFactory.php");
                $crs_obj = ilObjectFactory::getInstanceByRefId($hop["ref_id"]);
                break;
            }
        }
        return $crs_obj;
    }

    /**
     * Closure to get txt from plugin
     */
    public function txtClosure()
    {
        return function ($code) {
            return $this->txt($code);
        };
    }

    /**
     * Translate lang code into value
     *
     * @param string 	$code
     *
     * @return string
     */
    public function pluginTxt($code)
    {
        return parent::txt($code);
    }

    protected function getDIC()
    {
        return $GLOBALS["DIC"];
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
        foreach ($config as $key => $value) {
            if ($key == "update_idd_time") {
                if ($value === true) {
                    $this->updateIDDTime();
                }
            } elseif ($key == "set_idd_time") {
                if (is_numeric($value)) {
                    $this->setIddTime($value);
                }
            } elseif ($key == "update_gti_time") {
                $parent_crs = $this->getParentCourse();
                if (!is_null($parent_crs)) {
                    $this->getPlugin()->updateEduTrackings((int) $parent_crs->getRefId());
                }
            } elseif ($key == "update_gti") {
                $actions = $this->getActionsFor("GTI");
                $settings = $actions->select();
                $set_trainingtimes_manually = (bool) $value["set_trainingtimes_manually"];
                $minutes = $value["time"];
                if (!$set_trainingtimes_manually) {
                    $parent_crs = $this->getParentCourse();
                    if (!is_null($parent_crs)) {
                        $minutes = $this->getPlugin()
                            ->getCourseTrainingtimeInMinutes(
                                (int) $parent_crs->getRefId()
                            );
                    }
                }

                $settings = $settings->withCategoryId($value["categories"])
                    ->withSetTrainingTimeManually($set_trainingtimes_manually)
                    ->withMinutes($minutes);
                $actions->update($settings);
            } else {
                throw new \RuntimeException("Can't process configuration '$key'");
            }
        }
    }

    /**
     * Sets minutes for idd from agenda entries
     *
     * @return void
     */
    public function updateIDDTime()
    {
        $minutes = 0;
        $infos = $this->getCourseInfo(CourseInfo::CONTEXT_XETR_TIME_INFO, false);
        foreach ($infos as $key => $info) {
            $minutes += $info->getValue();
        }

        $actions = $this->getActionsFor("IDD");
        $settings = $actions->select();
        $settings = $settings->withMinutes($minutes);
        $settings->update();
    }

    /**
     * Sets minutes for idd
     *
     * @return void
     */
    public function setIDDTime($value)
    {
        $actions = $this->getActionsFor("IDD");
        $settings = $actions->select();
        $settings = $settings->withMinutes($value);
        $settings->update();
    }

    /**
     * @return \CaT\Ente\ILIAS\ProviderDB
     */
    protected function getProviderDB()
    {
        $DIC = $this->getDIC();
        return $DIC["ente.provider_db"];
    }

    /**
     * Get the ref_id of the object this object handles components for.
     *
     * @return int
     */
    protected function getEntityRefId()
    {
        return $this->getParentCourse()->getRefId();
    }

    public function getWBDDataInterface()
    {
        return new CaT\Plugins\EduTracking\Purposes\WBD\WBDDataInterface(
            $this->getActionsFor('WBD')->select(),
            $this->getConfigWBD(),
            $this->getWBDUserDataProvider(),
            $this->getWBDObjectProvider()
        );
    }

    public function getWBDObjectProvider()
    {
        global $DIC;
        return new WBD\IliasWBDObjectProvider($DIC['tree']);
    }

    public function getWBDUserDataProvider()
    {
        return new WBD\IliasWBDUserDataProvider();
    }

    public function getConfigWBD()
    {
        global $DIC;
        $db = new CaT\Plugins\EduTracking\Purposes\WBD\Configuration\ilDB($DIC['ilDB']);
        return $db->select();
    }

    public function getConfigIDD()
    {
        global $DIC;
        $db = new CaT\Plugins\EduTracking\Purposes\IDD\Configuration\ilDB($DIC['ilDB']);
        return $db->select();
    }

    public function getConfigGTI()
    {
        global $DIC;
        $db = new CaT\Plugins\EduTracking\Purposes\GTI\Configuration\ilDB($DIC['ilDB']);
        return $db->select();
    }
}

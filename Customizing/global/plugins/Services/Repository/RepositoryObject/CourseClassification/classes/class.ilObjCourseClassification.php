<?php
include_once("Services/Repository/classes/class.ilObjectPlugin.php");
require_once(__DIR__ . "/UnboundProvider.php");

use CaT\Plugins\CourseClassification;
use CaT\Ente\ILIAS\ilProviderObjectHelper;
use CaT\Ente\ILIAS\ilHandlerObjectHelper;
use ILIAS\TMS\CourseInfo;
use ILIAS\TMS\CourseInfoHelper;

/**
 * Object of the plugin
 */
class ilObjCourseClassification extends \ilObjectPlugin implements CourseClassification\ObjCourseClassification
{
    use ilProviderObjectHelper;
    use ilHandlerObjectHelper;
    use CourseInfoHelper;

    protected function getDIC()
    {
        return $GLOBALS["DIC"];
    }

    /**
     * @var CourseClassification\ilActions
     */
    protected $actions;

    /**
     * Init the type of the plugin. Same value as choosen in plugin.php
     */
    public function initType()
    {
        $this->setType("xccl");
    }

    public function doCreate()
    {
        $this->course_classification = $this->getActions()->createEmpty((int) $this->getId());
        $this->createUnboundProvider("crs", CourseClassification\UnboundProvider::class, __DIR__ . "/UnboundProvider.php");
    }

    /**
     * Get called if the object get be updated
     * Update additional setting values
     */
    public function doUpdate()
    {
        $this->getActions()->update($this->course_classification);
        $this->getActions()->raiseUpdateEvent();
    }

    /**
     * Get called after object creation to read further information
     */
    public function doRead()
    {
        $this->course_classification = $this->getActions()->selectFor((int) $this->getId());
    }

    /**
     * Get called if the object should be deleted.
     * Delete additional settings
     */
    public function doDelete()
    {
        $this->deleteUnboundProviders();
        $this->getActions()->deleteFor((int) $this->getId());
    }

    /**
     * Get called if the object get be coppied.
     * Copy additional settings to new object
     */
    public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
    {
        $new_obj->doRead();

        $fnc = function ($cc) {
            return $cc->withType($this->course_classification->getType())
                    ->withEduProgram($this->course_classification->getEduProgram())
                    ->withTopics($this->course_classification->getTopics())
                    ->withCategories($this->course_classification->getCategories())
                    ->withContent($this->course_classification->getContent())
                    ->withGoals($this->course_classification->getGoals())
                    ->withMethod($this->course_classification->getMethod())
                    ->withMedia($this->course_classification->getMedia())
                    ->withTargetGroup($this->course_classification->getTargetGroup())
                    ->withTargetGroupDescription($this->course_classification->getTargetGroupDescription())
                    ->withContact($this->course_classification->getContact())
                    ->withPreparation($this->course_classification->getPreparation())
                    ->withAdditionalLinks($this->course_classification->getAdditionalLinks());
        };

        $new_obj->updateCourseClassification($fnc);
        $new_obj->update();
    }

    /**
     * Get actions for repo object
     *
     * @return CourseClassification\ilActions
     */
    public function getActions()
    {
        if ($this->actions === null) {
            global $DIC;
            $this->actions = new CourseClassification\ilActions($this, $this->getSettingsDB(), $DIC["ilAppEventHandler"]);
        }

        return $this->actions;
    }

    /**
     * Get db for settings
     *
     * @return CourseClassification\Settings\DB
     */
    protected function getSettingsDB()
    {
        if ($this->settings_db === null) {
            global $DIC;
            $db = $DIC->database();
            $additional_links_db = new CourseClassification\AdditionalLinks\ilDB($db);
            $this->settings_db = new CourseClassification\Settings\ilDB(
                $db,
                $additional_links_db
            );
        }

        return $this->settings_db;
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
     * Get current course classification settings
     *
     * @return CourseClassification\Settings\CourseClassification
     */
    public function getCourseClassification()
    {
        return $this->course_classification;
    }

    public function updateCourseClassification(\Closure $update_function)
    {
        $this->course_classification = $update_function($this->course_classification);
    }

    /**
     * Get the directory of this plugin
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->plugin->getDirectory();
    }

    /**
     * Get information from course classification object
     *
     * @param ilObjCourseClassification 	$ccl
     *
     * @return array<integer, string | int[] | string[] | null>
     */
    public function getCourseClassificationValues()
    {
        $actions = $this->getActions();
        $settings = $this->getCourseClassification();

        $target_group = array();
        $topics = array();
        $type = "";

        $target_group_ids = $settings->getTargetGroup();
        if ($target_group_ids !== null) {
            $target_group = $actions->getTargetGroupNames($target_group_ids);
        }

        $topic_ids = $settings->getTopics();
        if ($topic_ids !== null) {
            $topics = $actions->getTopicsNames($topic_ids);
        }

        $type_id = $settings->getType();
        if ($type_id !== null) {
            $type = array_shift($actions->getTypeName($type_id));
        }

        $category_id = $settings->getCategories();
        if ($category_id !== null) {
            $category = array_shift($actions->getCategoryNames($category_id));
        }

        return array($type_id,
            $type,
            $target_group_ids,
            $target_group,
            (string) $settings->getGoals(),
            $topic_ids,
            $topics,
            $category_id,
            $category,
            (string) $settings->getContent()
        );
    }

    /**
     * @return \CaT\Ente\ILIAS\ProviderDB
     */
    protected function getProviderDB()
    {
        $DIC = $this->getDIC();
        return $DIC["ente.provider_db"];
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
        $this->doRead();
        foreach ($config as $key => $value) {
            switch ($key) {
                case "content":
                    $fnc = function ($cc) use ($value) {
                        return $cc->withContent($value);
                    };
                    $this->updateCourseClassification($fnc);
                    break;
                case "target_group":
                    $fnc = function ($cc) use ($value) {
                        return $cc->withTargetGroup($value);
                    };
                    $this->updateCourseClassification($fnc);
                    break;
                case "target_group_desc":
                    $fnc = function ($cc) use ($value) {
                        return $cc->withTargetGroupDescription($value);
                    };
                    $this->updateCourseClassification($fnc);
                    break;
                case "benefits":
                    $fnc = function ($cc) use ($value) {
                        return $cc->withGoals($value);
                    };
                    $this->updateCourseClassification($fnc);
                    break;
                case "topics":
                    if ($value === true) {
                        $infos = $this->getCourseInfo(CourseInfo::CONTEXT_XCCL_TOPICS, false);
                        foreach ($infos as $info) {
                            if ($info !== null) {
                                $topics = $info->getValue();
                                $old_topics = $this->getCourseClassification()->getTopics();
                                if (is_array($old_topics)) {
                                    $topics = array_unique(array_merge($old_topics, $topics));
                                }
                                $fnc = function ($cc) use ($topics) {
                                    return $cc->withTopics($topics);
                                };
                                $this->updateCourseClassification($fnc);
                            } else {
                                $this->updateCourseClassification(function ($cc) {
                                    return $cc;
                                });
                            }
                        }
                    }
                    break;
                default:
                    throw new \RuntimeException("Can't process configuration '$key'");
            }
        }

        $this->doUpdate();
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
     * Get the ref_id of the object this object handles components for.
     *
     * @return int
     */
    protected function getEntityRefId()
    {
        return $this->getParentCourse()->getRefId();
    }
}

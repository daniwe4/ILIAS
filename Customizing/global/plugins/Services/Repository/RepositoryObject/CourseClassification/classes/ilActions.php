<?php

namespace CaT\Plugins\CourseClassification;

/**
 * Communication class between front- and backend.
 * E.g. GUI only use this class to get information from ILIAS DB.
 */
class ilActions
{
    use Action;

    /**
     * @var ObjCourseClassification
     */
    protected $object;

    /**
     * @var Settings\DB
     */
    protected $settings_db;

    public function __construct(
        ObjCourseClassification $object,
        Settings\DB $settings_db,
        \ilAppEventHandler $app_event_handler
    ) {
        $this->object = $object;
        $this->settings_db = $settings_db;
        global $DIC;
        $this->app_event_handler = $app_event_handler;
    }

    /**
     * Get course classification for obj id
     *
     * @param int 	$obj_id
     *
     * @return Settings\CourseClassification
     */
    public function selectFor($obj_id)
    {
        assert('is_int($obj_id)');
        return $this->settings_db->selectFor($obj_id);
    }

    /**
     * Delete settings for obj id
     *
     * @param int 	$obj_id
     *
     * @return null
     */
    public function deleteFor($obj_id)
    {
        assert('is_int($obj_id)');
        return $this->settings_db->deleteFor($obj_id);
    }

    /**
     * Update course classification
     *
     * @param Settings\CourseClassification
     *
     * @return null
     */
    public function update(Settings\CourseClassification $course_classification)
    {
        $this->settings_db->update($course_classification);

        global $ilAppEventHandler;
        $ilAppEventHandler->raise(
             'Plugin/CourseClassification',
             'update',
             array(
                 'ref_id' => $this->object->getRefId(),
                 'data' => $course_classification
             )
         );
    }

    /**
     * Get the repo object
     *
     * @return ObjCourseClassification
     */
    public function getObject()
    {
        if ($this->object === null) {
            throw new \LogicException("No object was set");
        }

        return $this->object;
    }

    /**
     * Create a course classification entry in db with needed values
     *
     * @param int 	$obj_id
     *
     * @return Settings\CourseClassification
     */
    public function createEmpty($obj_id)
    {
        return $this->settings_db->create($obj_id);
    }

    /**
     * Get form options for type
     *
     * @return string[]
     */
    public function getTypeOptions()
    {
        return $this->settings_db->getFormOptionsByTableName(Settings\ilDB::TABLE_TYPE);
    }

    /**
     * Get form options for edu programme
     *
     * @return string[]
     */
    public function getEduProgramOptions()
    {
        return $this->settings_db->getFormOptionsByTableName(Settings\ilDB::TABLE_EDU_PROGRAM);
    }

    /**
     * Get form options for topic
     *
     * @return string[]
     */
    public function getCategoryOptions()
    {
        return $this->settings_db->getFormOptionsByTableName(Settings\ilDB::TABLE_CATEGORY);
    }

    /**
     * Get form options for method
     *
     * @return string[]
     */
    public function getMethodOptions()
    {
        return $this->settings_db->getFormOptionsByTableName(Settings\ilDB::TABLE_METHOD);
    }

    /**
     * Get form options for media
     *
     * @return string[]
     */
    public function getMediaOptions()
    {
        return $this->settings_db->getFormOptionsByTableName(Settings\ilDB::TABLE_MEDIA);
    }

    /**
     * Get form options for target group
     *
     * @return string[]
     */
    public function getTargetGroupOptions()
    {
        return $this->settings_db->getFormOptionsByTableName(Settings\ilDB::TABLE_TARGET_GROUP);
    }

    /**
     * Get form options for topics
     *
     * @return string[]
     */
    public function getTopicOptions()
    {
        return $this->settings_db->getTopicGroupOptions();
    }

    /**
     * Get name for selected type
     *
     * TODO: This should not return an array but a string. Course may only have one type.
     *
     * @param int 	$type_id
     *
     * @return string[]
     */
    public function getTypeName($type_id)
    {
        assert('is_int($type_id)');
        return $this->settings_db->getOptionsNameByTableName(Settings\ilDB::TABLE_TYPE, array($type_id));
    }

    /**
     * Get name for selected edu programme
     *
     * @param int 	$edu_program_id
     *
     * @return string[]
     */
    public function getEduProgramName($edu_program_id)
    {
        assert('is_int($edu_program_id)');
        return $this->settings_db->getOptionsNameByTableName(Settings\ilDB::TABLE_EDU_PROGRAM, array($edu_program_id));
    }

    /**
     * Get name for selected category
     *
     * @param int[] 	$category_ids
     *
     * @return string[]
     */
    public function getCategoryNames($category_ids)
    {
        if (is_null($category_ids)) {
            $category_ids = array();
        }
        return $this->settings_db->getOptionsNameByTableName(Settings\ilDB::TABLE_CATEGORY, $category_ids);
    }

    /**
     * Get categories ids by topic ids
     *
     * @param int[] | null
     *
     * @return int[] | null
     */
    public function getCategoriesByTopicIds(array $topic_ids = null)
    {
        if ($topic_ids === null) {
            return null;
        }

        return $this->settings_db->getCategoriesByTopicIds($topic_ids);
    }

    /**
     * Raises update event
     *
     * @return void
     */
    public function raiseUpdateEvent()
    {
        $e["cc_obj_ids"] = array($this->getObject()->getId());
        $this->app_event_handler->raise("Plugin/CourseClassification", "updateCCObject", $e);
    }
}

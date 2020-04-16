<?php

namespace CaT\Plugins\CourseClassification;

/**
 * Communication class between front- and backend.
 * E.g. GUI only use this class to get information from ILIAS DB.
 */
class ilPluginActions
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
        \ilCourseClassificationPlugin $object,
        Settings\DB $settings_db
    ) {
        $this->object = $object;
        $this->settings_db = $settings_db;
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
     * Get name for selected topics
     *
     * @param int[] 	$topic_ids
     *
     * @return string[]
     */
    public function getTopicNames($topic_ids)
    {
        if (is_null($topic_ids)) {
            $topic_ids = array();
        }
        return $this->settings_db->getOptionsNameByTableName(Settings\ilDB::TABLE_TOPIC, $topic_ids);
    }
}

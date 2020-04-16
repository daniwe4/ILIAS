<?php
namespace CaT\Plugins\CourseClassification;

/**
 * Trait Action
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
trait Action
{
    /**
     * Get names for selected methods
     *
     * @param int[] | null	$method_ids
     *
     * @return string[]
     */
    public function getMethodNames(array $method_ids = null)
    {
        if (is_null($method_ids)) {
            $method_ids = array();
        }
        return $this->settings_db->getOptionsNameByTableName(Settings\ilDB::TABLE_METHOD, $method_ids);
    }

    /**
     * Get names for selected media
     *
     * @param int[] | null	$media_ids
     *
     * @return string[]
     */
    public function getMediaNames(array $media_ids = null)
    {
        if (is_null($media_ids)) {
            $media_ids = array();
        }
        return $this->settings_db->getOptionsNameByTableName(Settings\ilDB::TABLE_MEDIA, $media_ids);
    }

    /**
     * Get names for selected target group
     *
     * @param int[] | null	$media_ids
     *
     * @return string[]
     */
    public function getTargetGroupNames(array $target_group_ids = null)
    {
        if (is_null($target_group_ids)) {
            $target_group_ids = array();
        }
        return $this->settings_db->getOptionsNameByTableName(Settings\ilDB::TABLE_TARGET_GROUP, $target_group_ids);
    }

    /**
     * Get names for selected topics
     *
     * @param int[] | null	$topic_ids
     *
     * @return string[]
     */
    public function getTopicsNames(array $topic_ids = null)
    {
        if (is_null($topic_ids)) {
            $topic_ids = array();
        }
        return $this->settings_db->getOptionsNameByTableName(Settings\ilDB::TABLE_TOPIC, $topic_ids);
    }
}

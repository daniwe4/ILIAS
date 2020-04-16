<?php

namespace CaT\Plugins\CopySettings;

/**
 * Communication class between front- and backend.
 * E.g. GUI only use this class to get information from ILIAS DB.
 */
class ilObjectActions
{
    public function __construct($object, $children_db)
    {
        $this->object = $object;
        $this->children_db = $children_db;
    }

    /**
     * Select copy settings for object
     *
     * @return Settings[]
     */
    public function select()
    {
        return $this->children_db->select($this->getId());
    }

    /**
     * Get single settings for child
     *
     * @param int 	$ref_id
     *
     * @return Settings
     */
    public function getCopySettingsByRefId($ref_id)
    {
        assert('is_int($ref_id)');
        return array_shift(array_filter($this->getObject()->getSettings(), function ($setting) use ($ref_id) {
            if ($ref_id == $setting->getTargetRefId()) {
                return $setting;
            }
        }));
    }

    /**
     * Mark parent container with template prefix
     *
     * @param string 	$prefix
     *
     * @return void
     */
    public function markParentAsTemplate($prefix)
    {
        assert('is_string($prefix)');
        $parent = $this->object->getParentContainer();
        $parent->setTitle($prefix . " " . $parent->getTitle());
        $parent->update();
    }

    /**
     * Mark parent container with template prefix
     *
     * @param string 	$prefix
     *
     * @return void
     */
    public function unmarkParentAsTemplate($prefix)
    {
        assert('is_string($prefix)');
        $parent = $this->getObject()->getParentContainer();
        $parent->setTitle(trim(str_replace($prefix, "", $parent->getTitle())));
        $parent->update();
    }

    /**
     * Get ref id of current object
     *
     * @return int
     */
    public function getRefId()
    {
        return (int) $this->getObject()->getRefId();
    }

    /**
     * Get object id of current object
     *
     * @return int
     */
    public function getId()
    {
        return (int) $this->getObject()->getId();
    }

    /**
     * Get the current object
     *
     * @return Settings
     */
    public function getObject()
    {
        if ($this->object === null) {
            throw new \Exception("No object set");
        }

        return $this->object;
    }

    /**
     * Get the parent container object of copy settings
     *
     * @return ilObjCourse | ilObjCategory
     */
    public function getParentContainer()
    {
        return $this->getObject()->getParentContainer();
    }

    /**
     * Clear all copy settings of current object
     *
     * @return void
     */
    public function clearCopySettings()
    {
        $this->children_db->delete($this->getId());
    }

    /**
     * Create a new copy setting entry
     *
     * @param int 	$obj_id
     * @param int 	$ref_id
     * @param bool 	$is_referenced
     * @param string 	$process_type
     *
     * @return void
     */
    public function createCopySettings($ref_id, $obj_id, $is_referenced, $process_type)
    {
        assert('is_int($obj_id)');
        assert('is_int($ref_id)');
        assert('is_bool($is_referenced)');
        assert('is_string($process_type)');

        $this->children_db->create($this->getId(), $ref_id, $obj_id, $is_referenced, $process_type);
    }
}

<?php

declare(strict_types=1);

namespace CaT\Plugins\EduTracking\Purposes\GTI;

class GTI
{
    /**
     * @var \ilObjEduTracking
     */
    protected $obj;

    /**
     * @var DB
     */
    protected $db;

    /**
     * @var int
     */
    protected $category_id;

    /**
     * @var int
     */
    protected $set_trainingtime_manually;

    /**
     * @var int
     */
    protected $minutes;

    public function __construct(
        DB $db,
        \ilAppEventHandler $evt_handler,
        \ilObjEduTracking $obj,
        int $category_id = null,
        bool $set_trainingtime_manually = false,
        int $minutes = 0
    ) {
        $this->db = $db;
        $this->evt_handler = $evt_handler;
        $this->obj = $obj;
        $this->category_id = $category_id;
        $this->set_trainingtime_manually = $set_trainingtime_manually;
        $this->minutes = $minutes;
    }

    /**
     * @return int
     */
    public function getObjId()
    {
        return $this->obj->getId();
    }

    /**
     * @return ilObjEduTracking
     */
    public function getObject()
    {
        return $this->obj;
    }

    public function getCategoryId()
    {
        return $this->category_id;
    }

    public function withCategoryId(int $category_id = null) : GTI
    {
        $clone = clone $this;
        $clone->category_id = $category_id;
        return $clone;
    }

    public function getSetTrainingTimeManually() : bool
    {
        return $this->set_trainingtime_manually;
    }

    public function withSetTrainingTimeManually(bool $set_trainingtime_manually) : GTI
    {
        $clone = clone $this;
        $clone->set_trainingtime_manually = $set_trainingtime_manually;
        return $clone;
    }

    public function getMinutes() : int
    {
        return $this->minutes;
    }

    public function withMinutes(int $minutes = null) : GTI
    {
        $clone = clone $this;
        $clone->minutes = $minutes;
        return $clone;
    }

    /**
     * Persist any changes performed on this object.
     *
     * @return	void
     */
    public function update()
    {
        $parent_course = $this->getObject()->getParentCourse();
        $parent_course_id = 0;
        if ($parent_course) {
            $parent_course_id = (int) $parent_course->getId();
        }
        $this->db->update($this);
        $category = $this->category_id > 0 ? $this->getObject()->getActionsFor("GTI")->getConfigActions()->getTitleById($this->category_id) : '';

        $this->throwEvent(
            "updateGTI",
            (int) $this->getObject()->getId(),
            $this->getMinutes(),
            $parent_course_id,
            $category
        );
    }

    /**
     * Delete any persisted data associated with this object.
     *
     * @return	void
     */
    public function delete()
    {
        $this->db->deleteFor($this->obj);
    }

    protected function throwEvent(string $reason, int $obj_id, int $minutes, int $crs_id, string $gti_category)
    {
        $payload = [
            "xetr_obj_id" => $obj_id,
            "gti_learning_time" => $minutes,
            "crs_id" => $crs_id,
            'gti_category' => $gti_category
        ];
        $this->evt_handler->raise("Plugin/EduTracking", $reason, $payload);
    }
}

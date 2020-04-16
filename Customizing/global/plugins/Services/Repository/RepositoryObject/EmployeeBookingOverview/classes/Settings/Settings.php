<?php

declare(strict_types=1);

namespace CaT\Plugins\EmployeeBookingOverview\Settings;

/**
 * This is the object for additional settings.
 *
 * @author
 * @copyright Extended GPL, see LICENSE
 */
class Settings
{
    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var bool
     */
    protected $online;

    /**
     * @var bool
     */
    protected $global;

    protected $invisible_course_topics;

    public function __construct(
        int $obj_id,
        bool $online = false,
        bool $global = false,
        array $invisible_course_topics = []
    ) {
        $this->obj_id = $obj_id;
        $this->online = $online;
        $this->global = $global;
        $this->invisible_course_topics = $invisible_course_topics;
    }

    public function objId() : int
    {
        return $this->obj_id;
    }

    public function isOnline() : bool
    {
        return $this->online;
    }

    public function isGlobal() : bool
    {
        return $this->global;
    }

    public function getInvisibleCourseTopics() : array
    {
        return $this->invisible_course_topics;
    }

    public function withOnline(bool $online) : Settings
    {
        $other = clone $this;
        $other->online = $online;
        return $other;
    }

    public function withGlobal(bool $global) : Settings
    {
        $other = clone $this;
        $other->global = $global;
        return $other;
    }

    public function withInvisibleCourseTopics(array $invisible_course_topics) : Settings
    {
        $other = clone $this;
        $other->invisible_course_topics = $invisible_course_topics;
        return $other;
    }
}

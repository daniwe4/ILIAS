<?php

declare(strict_types=1);

namespace CaT\Plugins\EduBiography\Settings;

use CaT\Plugins\UserBookings\Settings\UserBookingsSettings;

class Settings
{
    protected $id;
    protected $is_online;
    protected $has_superior_overview;
    /**
     * @var array
     */
    protected $init_visible_columns;

    /**
     * @var array
     */
    protected $invisible_course_topics;

    /**
     * @var bool
     */
    protected $recommendation_allowed;

    public function __construct(
        int $id,
        bool $is_online,
        bool $has_superior_overview,
        array $init_visible_columns,
        array $invisible_course_topics,
        bool $recommendation_allowed = false
    ) {
        $this->id = $id;
        $this->is_online = $is_online;
        $this->has_superior_overview = $has_superior_overview;
        $this->init_visible_columns = $init_visible_columns;
        $this->invisible_course_topics = $invisible_course_topics;
        $this->recommendation_allowed = $recommendation_allowed;
    }

    public function id() : int
    {
        return $this->id;
    }

    public function isOnline() : bool
    {
        return $this->is_online;
    }

    public function hasSuperiorOverview() : bool
    {
        return $this->has_superior_overview;
    }

    public function getInitVisibleColumns() : array
    {
        return $this->init_visible_columns;
    }

    public function getInvisibleCourseTopics() : array
    {
        return $this->invisible_course_topics;
    }

    public function withIsOnline(bool $is_online) : Settings
    {
        $other = clone $this;
        $other->is_online = $is_online;
        return $other;
    }

    public function withHasSuperiorOverview(bool $has_superior_overview) : Settings
    {
        $other = clone $this;
        $other->has_superior_overview = $has_superior_overview;
        return $other;
    }

    public function withInitVisibleColumns(array $init_visible_columns) : Settings
    {
        $other = clone $this;
        $other->init_visible_columns = $init_visible_columns;
        return $other;
    }

    public function withInvisibleCourseTopics(array $invisible_course_topics) : Settings
    {
        $other = clone $this;
        $other->invisible_course_topics = $invisible_course_topics;
        return $other;
    }

    public function getRecommendationAllowed() : bool
    {
        return $this->recommendation_allowed;
    }

    public function withRecommendationAllowed(bool $allowed) : Settings
    {
        $clone = clone $this;
        $clone->recommendation_allowed = $allowed;
        return $clone;
    }
}

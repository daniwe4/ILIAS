<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\TrainingSearch\Settings;

class Settings
{
    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var bool
     */
    protected $is_online;

    /**
     * @var bool
     */
    protected $is_local;

    /**
     * @var array
     */
    protected $relevant_topics;

    /**
     * @var array
     */
    protected $relevant_categories;

    /**
     * @var array
     */
    protected $relevant_target_groups;

    /**
     * @var bool
     */
    protected $is_recommendation_allowed;

    public function __construct(
        int $obj_id,
        bool $is_online,
        bool $is_local = false,
        array $relevant_topics = [],
        array $relevant_categories = [],
        array $relevant_target_groups = [],
        bool $is_recommendation_allowed = false
    ) {
        $this->obj_id = $obj_id;
        $this->is_online = $is_online;
        $this->is_local = $is_local;
        $this->relevant_topics = $relevant_topics;
        $this->relevant_categories = $relevant_categories;
        $this->relevant_target_groups = $relevant_target_groups;
        $this->is_recommendation_allowed = $is_recommendation_allowed;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    public function getIsOnline() : bool
    {
        return $this->is_online;
    }

    public function isLocal() : bool
    {
        return $this->is_local;
    }

    public function relevantTopics() : array
    {
        return $this->relevant_topics;
    }

    public function relevantCategories() : array
    {
        return $this->relevant_categories;
    }

    public function relevantTargetGroups() : array
    {
        return $this->relevant_target_groups;
    }

    public function isRecommendationAllowed() : bool
    {
        return $this->is_recommendation_allowed;
    }

    public function withIsOnline(bool $is_online) : Settings
    {
        $clone = clone $this;
        $clone->is_online = $is_online;
        return $clone;
    }

    public function withIsLocal(bool $is_local) : Settings
    {
        $clone = clone $this;
        $clone->is_local = $is_local;
        return $clone;
    }

    public function withRelevantTopics(array $relevant_topics) : Settings
    {
        $clone = clone $this;
        $clone->relevant_topics = $relevant_topics;
        return $clone;
    }

    public function withRelevantCategories(array $relevant_categories) : Settings
    {
        $clone = clone $this;
        $clone->relevant_categories = $relevant_categories;
        return $clone;
    }

    public function withRelevantTargetGroups(array $relevant_target_groups) : Settings
    {
        $clone = clone $this;
        $clone->relevant_target_groups = $relevant_target_groups;
        return $clone;
    }

    public function withIsRecommendationAllowed(bool $is_recommendation_allowed) : Settings
    {
        $clone = clone $this;
        $clone->is_recommendation_allowed = $is_recommendation_allowed;
        return $clone;
    }
}

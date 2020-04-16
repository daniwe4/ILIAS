<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\TrainingSearch\Search;

/**
 * Options for a search of trainings.
 */
class Options
{
    const SORTATION_TITLE_ASC = "sortation_title_asc";
    const SORTATION_TITLE_DESC = "sortation_title_desc";
    const SORTATION_PERIOD_ASC = "sortation_period_asc";
    const SORTATION_PERIOD_DESC = "sortation_period_desc";
    const SORTATION_CITY_ASC = "sortation_city_asc";
    const SORTATION_CITY_DESC = "sortation_city_desc";
    const SORTATION_DEFAULT = "sortation_period_asc";

    protected static $options = [
        self::SORTATION_TITLE_ASC,
        self::SORTATION_TITLE_DESC,
        self::SORTATION_PERIOD_ASC,
        self::SORTATION_PERIOD_DESC,
        self::SORTATION_CITY_ASC,
        self::SORTATION_CITY_DESC
    ];

    /**
     * @var	int
     */
    protected $user_id;

    /**
     * @var int
     */
    protected $category_ref_id;

    /**
     * @var	string
     */
    protected $sortation;

    /**
     * @var string|null
     */
    protected $text_filter;

    /**
     * @var string|null
     */
    protected $type_filter;

    /**
     * @var	string|null
     */
    protected $topic_filter;

    /**
     * @var string|null
     */
    protected $category_filter;

    /**
     * @var string|null
     */
    protected $target_groups_filter;

    /**
     * @var \DateTime|null
     */
    protected $duration_start_filter;

    /**
     * @var \DateTime|null
     */
    protected $duration_end_filter;

    /**
     * @var bool
     */
    protected $only_bookable_filter = false;

    /**
     * @var bool
     */
    protected $idd_relevant_filter = false;

    /**
     * @var int | null
     */
    protected $search_tag;

    public function __construct(int $user_id, int $category_ref_id, string $sortation = self::SORTATION_PERIOD_ASC)
    {
        if ($user_id <= 0) {
            throw new \InvalidArgumentException("Invalid user_id: $user_id");
        }
        if ($category_ref_id <= 0) {
            throw new \InvalidArgumentException("Invalid category_ref_id: $user_id");
        }

        if (!in_array($sortation, self::$options)) {
            throw new \InvalidArgumentException("Invalid sortation: $sortation");
        }

        $this->user_id = $user_id;
        $this->category_ref_id = $category_ref_id;
        $this->sortation = $sortation;
    }

    public function getUserId() : int
    {
        return $this->user_id;
    }

    public function getCategoryRefId() : int
    {
        return $this->category_ref_id;
    }

    public function getSortation() : string
    {
        return $this->sortation;
    }

    public function withSortation(string $sortation) : Options
    {
        if (!in_array($sortation, self::$options)) {
            throw new \InvalidArgumentException("Invalid sortation: $sortation");
        }

        $clone = clone $this;
        $clone->sortation = $sortation;
        return $clone;
    }

    /**
     * @return string|null
     */
    public function getTextFilter()
    {
        return $this->text_filter;
    }

    public function withTextFilter(string $text_filter) : Options
    {
        $clone = clone $this;
        $clone->text_filter = $text_filter;
        return $clone;
    }

    /**
     * @return int|null
     */
    public function getTypeFilter()
    {
        return $this->type_filter;
    }

    public function withTypeFilter(string $type_filter) : Options
    {
        $clone = clone $this;
        $clone->type_filter = $type_filter;
        return $clone;
    }

    /**
     * @return int|null
     */
    public function getTopicFilter()
    {
        return $this->topic_filter;
    }

    public function withTopicFilter(string $topic_filter) : Options
    {
        $clone = clone $this;
        $clone->topic_filter = $topic_filter;
        return $clone;
    }

    /**
     * @return int|null
     */
    public function getCategoryFilter()
    {
        return $this->category_filter;
    }

    public function withCategoryFilter(string $category_filter) : Options
    {
        $clone = clone $this;
        $clone->category_filter = $category_filter;
        return $clone;
    }

    public function getTargetGroupsFilter()
    {
        return $this->target_groups_filter;
    }

    public function withTargetGroupsFilter(string $target_groups_filter) : Options
    {
        $clone = clone $this;
        $clone->target_groups_filter = $target_groups_filter;
        return $clone;
    }

    /**
     * @return \DateTime|null
     */
    public function getDurationStartFilter()
    {
        return $this->duration_start_filter;
    }

    /**
     * @return \DateTime|null
     */
    public function getDurationEndFilter()
    {
        return $this->duration_end_filter;
    }

    public function withDurationFilter(\DateTime $duration_start_filter, \DateTime $duration_end_filter) : Options
    {
        $clone = clone $this;
        $clone->duration_start_filter = $duration_start_filter;
        $clone->duration_end_filter = $duration_end_filter;
        return $clone;
    }

    public function getOnlyBookableFilter() : bool
    {
        return $this->only_bookable_filter;
    }

    public function withOnlyBookableFilter(bool $only_bookable_filter) : Options
    {
        $clone = clone $this;
        $clone->only_bookable_filter = $only_bookable_filter;
        return $clone;
    }

    public function getIDDRelevantFilter() : bool
    {
        return $this->idd_relevant_filter;
    }

    public function withIDDRelevantFilter(bool $idd_relevant_filter) : Options
    {
        $clone = clone $this;
        $clone->idd_relevant_filter = $idd_relevant_filter;
        return $clone;
    }

    /**
     * @return int | null
     */
    public function getVenueSearchTagFilter()
    {
        return $this->search_tag;
    }

    public function withVenueSearchTagFilter(int $search_tag)
    {
        $clone = clone $this;
        $clone->search_tag = $search_tag;
        return $clone;
    }

    /**
     * Returns a hash over these Options that allows to identify similar
     * options. Starts with $userid_ for better identification.
     */
    public function getHash() : string
    {
        return "{$this->user_id}_" . hash("sha256", serialize($this));
    }
}

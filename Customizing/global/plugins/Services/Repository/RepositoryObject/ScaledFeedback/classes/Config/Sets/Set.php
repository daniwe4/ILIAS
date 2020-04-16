<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\ScaledFeedback\Config\Sets;

use CaT\Plugins\ScaledFeedback\Config\Dimensions\Dimension;

class Set
{
    /**
     * @var int
     */
    protected $set_id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $introtext;

    /**
     * @var string
     */
    protected $extrotext;

    /**
     * @var string
     */
    protected $repeattext;

    /**
     * @var bool
     */
    protected $is_locked;

    /**
     * @var int
     */
    protected $min_submissions;

    /**
     * @var bool
     */
    protected $is_used;

    /**
     * @var Dimension[]
     */
    protected $dimensions = array();

    public function __construct(
        int $set_id,
        string $title,
        string $introtext = "",
        string $extrotext,
        string $repeattext,
        bool $is_locked = false,
        int $min_submissions = 6,
        bool $is_used = false,
        array $dimensions = []
    ) {
        $this->set_id = $set_id;
        $this->title = $title;
        $this->introtext = $introtext;
        $this->extrotext = $extrotext;
        $this->repeattext = $repeattext;
        $this->is_locked = $is_locked;
        $this->min_submissions = $min_submissions;
        $this->is_used = $is_used;

        foreach ($dimensions as $dimension) {
            $this->dimensions[$dimension->getDimId()] = $dimension;
        }
    }

    public function getSetId() : int
    {
        return $this->set_id;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function withTitle(string $value) : Set
    {
        $clone = clone $this;
        $clone->title = $value;
        return $clone;
    }

    public function getIntrotext() : string
    {
        return $this->introtext;
    }

    public function withIntrotext(string $value) : Set
    {
        $clone = clone $this;
        $clone->introtext = $value;
        return $clone;
    }

    public function getExtrotext() : string
    {
        return $this->extrotext;
    }

    public function withExtrotext(string $value) : Set
    {
        $clone = clone $this;
        $clone->extrotext = $value;
        return $clone;
    }

    public function getRepeattext() : string
    {
        return $this->repeattext;
    }

    public function withRepeattext(string $value) : Set
    {
        $clone = clone $this;
        $clone->repeattext = $value;
        return $clone;
    }

    public function getIsLocked() : bool
    {
        return $this->is_locked;
    }

    public function withIsLocked(bool $value) : Set
    {
        $clone = clone $this;
        $clone->is_locked = $value;
        return $clone;
    }

    public function getMinSubmissions() : int
    {
        return $this->min_submissions;
    }

    public function withMinSubmissions(int $value) : Set
    {
        $clone = clone $this;
        $clone->min_submissions = $value;
        return $clone;
    }

    public function getIsUsed() : bool
    {
        return $this->is_used;
    }

    public function withIsUsed(bool $value) : Set
    {
        $clone = clone $this;
        $clone->is_used = $value;
        return $clone;
    }

    /**
     * @return 	Dimension[]
     */
    public function getDimensions() : array
    {
        return $this->dimensions;
    }

    public function resetDimensions()
    {
        $this->dimensions = array();
    }

    public function withRemovedDimensionById(int $id) : Set
    {
        $clone = clone $this;
        unset($this->dimensions[$id]);
        $clone->dimensions = $this->dimensions;
        return $clone;
    }

    public function withAdditionalDimension(Dimension $dimension) : Set
    {
        $clone = clone $this;
        $clone->dimensions[$dimension->getDimId()] = $dimension;
        return $clone;
    }
}

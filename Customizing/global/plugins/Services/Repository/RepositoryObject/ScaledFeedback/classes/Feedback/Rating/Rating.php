<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\ScaledFeedback\Feedback\Rating;

class Rating
{
    /**
     * @var string
     */
    protected $dim_title;

    /**
     * @var string[]
     */
    protected $captions;

    /**
     * @var string
     */
    protected $byline;

    /**
     * @var int
     */
    protected $dim_id;

    /**
     * @var int
     */
    protected $rating;

    /**
     * @var bool
     */
    protected $enable_comment;

    /**
     * @var string
     */
    protected $text;

    public function __construct(
        string $dim_title = "",
        array $captions = [],
        string $byline = "",
        int $dim_id = 0,
        int $rating = 0,
        bool $enable_comment = false,
        string $text = ""
    ) {
        $this->dim_title = $dim_title;
        $this->captions = $captions;
        $this->byline = $byline;
        $this->dim_id = $dim_id;
        $this->rating = $rating;
        $this->enable_comment = $enable_comment;
        $this->text = $text;
    }

    public function getDimTitle() : string
    {
        return $this->dim_title;
    }

    public function withDimTitle(string $dim_title) : Rating
    {
        $clone = clone $this;
        $clone->dim_title = $dim_title;
        return $clone;
    }

    public function getCaptions() : array
    {
        return $this->captions;
    }

    public function withCaptions(array $captions) : Rating
    {
        $clone = clone $this;
        $clone->captions = $captions;
        return $clone;
    }

    public function getByline() : string
    {
        return $this->byline;
    }

    public function withByline(string $byline) : Rating
    {
        $clone = clone $this;
        $clone->byline = $byline;
        return $clone;
    }

    public function getDimId() : int
    {
        return $this->dim_id;
    }

    public function withDimId(int $dim_id) : Rating
    {
        $clone = clone $this;
        $clone->dim_id = $dim_id;
        return $clone;
    }

    public function getRating() : int
    {
        return $this->rating;
    }

    public function withRating(int $rating) : Rating
    {
        $clone = $this;
        $clone->rating = $rating;
        return $clone;
    }

    public function getEnableComment() : bool
    {
        return $this->enable_comment;
    }

    public function withEnableComment(bool $value) : Rating
    {
        $clone = clone $this;
        $clone->enable_comment = $value;
        return $clone;
    }

    public function getText() : string
    {
        return $this->text;
    }

    public function withText(string $text) : Rating
    {
        $clone = $this;
        $clone->text = $text;
        return $clone;
    }
}

<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\ScaledFeedback\Feedback;

use CaT\Plugins\ScaledFeedback\Config\Sets\Set;
use CaT\Plugins\ScaledFeedback\Config\Dimensions\Dimension;

class Feedback
{
    /**
     * @var int|null
     */
    protected $parent_obj_id;

    /**
     * @var int|null
     */
    protected $parent_ref_id;

    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var int
     */
    protected $set_id;

    /**
     * @var int
     */
    protected $usr_id;

    /**
     * @var int
     */
    protected $dim_id;

    /**
     * @var int
     */
    protected $rating;

    /**
     * @var string
     */
    protected $commenttext;

    public function __construct(
        int $obj_id,
        int $set_id,
        int $usr_id,
        int $dim_id,
        int $rating,
        string $commenttext,
        int $parent_obj_id = null,
        int $parent_ref_id = null
    ) {
        $this->obj_id = $obj_id;
        $this->set_id = $set_id;
        $this->usr_id = $usr_id;
        $this->dim_id = $dim_id;
        $this->rating = $rating;
        $this->commenttext = $commenttext;
        $this->parent_obj_id = $parent_obj_id;
        $this->parent_ref_id = $parent_ref_id;
    }

    /**
     * @return 	int|null
     */
    public function getParentObjId()
    {
        return $this->parent_obj_id;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    public function withObjId(int $value) : Feedback
    {
        assert('is_int($value)');
        $clone = clone $this;
        $clone->obj_id = $value;
        return $clone;
    }

    public function getSetId() : int
    {
        return $this->set_id;
    }

    public function withSetId(int $value) : Feedback
    {
        $clone = clone $this;
        $clone->set_id = $value;
        return $clone;
    }

    public function getUsrId() : int
    {
        return $this->usr_id;
    }

    public function withUsrId(int $value) : Feedback
    {
        $clone = clone $this;
        $clone->usr_id = $value;
        return $clone;
    }

    public function getDimId() : int
    {
        return $this->dim_id;
    }

    public function withDimId(int $value) : Feedback
    {
        $clone = clone $this;
        $clone->dim_id = $value;
        return $clone;
    }

    public function getRating() : int
    {
        return $this->rating;
    }

    public function withRating(int $value) : Feedback
    {
        $clone = clone $this;
        $clone->rating = $value;
        return $clone;
    }

    public function getCommenttext() : string
    {
        return $this->commenttext;
    }

    public function withCommenttext(string $value) : Feedback
    {
        $clone = clone $this;
        $clone->commenttext = $value;
        return $clone;
    }

    public function withParentObjId(int $value = null) : Feedback
    {
        $clone = clone $this;
        $clone->parent_obj_id = $value;
        return $clone;
    }

    /**
     * @return 	int|null
     */
    public function getParentRefId()
    {
        return $this->parent_ref_id;
    }

    public function withParentRefId(int $value = null) : Feedback
    {
        $clone = clone $this;
        $clone->parent_ref_id = $value;
        return $clone;
    }
}

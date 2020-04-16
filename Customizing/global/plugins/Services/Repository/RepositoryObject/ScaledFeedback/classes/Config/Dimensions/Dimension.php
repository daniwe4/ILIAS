<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\ScaledFeedback\Config\Dimensions;

class Dimension
{
    /**
     * @var int
     */
    protected $dim_id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $displayed_title;

    /**
     * @var string
     */
    protected $info;

    /**
     * @var string
     */
    protected $label1;

    /**
     * @var string
     */
    protected $label2;

    /**
     * @var string
     */
    protected $label3;

    /**
     * @var string
     */
    protected $label4;

    /**
     * @var string
     */
    protected $label5;

    /**
     * @var bool
     */
    protected $is_locked;

    /**
     * @var bool
     */
    protected $enable_comment;

    /**
     * @var bool
     */
    protected $only_textual_feedback;

    /**
     * @var int
     */
    protected $ordernumber;

    /**
     * @var bool
     */
    protected $is_used;

    public function __construct(
        int $dim_id,
        string $title,
        string $displayed_title,
        string $info = "",
        string $label1 = "",
        string $label2 = "",
        string $label3 = "",
        string $label4 = "",
        string $label5 = "",
        bool $enable_comment = false,
        bool $only_textual_feedback = false,
        bool $is_locked = false,
        bool $is_used = false,
        int $ordernumber = 0
    ) {
        $this->dim_id = $dim_id;
        $this->title = $title;
        $this->displayed_title = $displayed_title;
        $this->info = $info;
        $this->label1 = $label1;
        $this->label2 = $label2;
        $this->label3 = $label3;
        $this->label4 = $label4;
        $this->label5 = $label5;
        $this->enable_comment = $enable_comment;
        $this->only_textual_feedback = $only_textual_feedback;
        $this->is_locked = $is_locked;
        $this->is_used = $is_used;
        $this->ordernumber = $ordernumber;
    }

    public function getDimId() : int
    {
        return $this->dim_id;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function withTitle(string $value) : Dimension
    {
        $clone = clone $this;
        $clone->title = $value;
        return $clone;
    }

    public function getDisplayedTitle() : string
    {
        return $this->displayed_title;
    }

    public function withDisplayedTitle(string $value) : Dimension
    {
        $clone = clone $this;
        $clone->displayed_title = $value;
        return $clone;
    }

    public function getInfo() : string
    {
        return $this->info;
    }

    public function withInfo(string $value) : Dimension
    {
        $clone = clone $this;
        $clone->info = $value;
        return $clone;
    }

    public function getLabel1() : string
    {
        return $this->label1;
    }

    public function withLabel1(string $value) : Dimension
    {
        $clone = clone $this;
        $clone->label1 = $value;
        return $clone;
    }

    public function getLabel2() : string
    {
        return $this->label2;
    }

    public function withLabel2(string $value) : Dimension
    {
        $clone = clone $this;
        $clone->label2 = $value;
        return $clone;
    }

    public function getLabel3() : string
    {
        return $this->label3;
    }

    public function withLabel3(string $value) : Dimension
    {
        $clone = clone $this;
        $clone->label3 = $value;
        return $clone;
    }

    public function getLabel4() : string
    {
        return $this->label4;
    }

    public function withLabel4(string $value) : Dimension
    {
        $clone = clone $this;
        $clone->label4 = $value;
        return $clone;
    }

    public function getLabel5() : string
    {
        return $this->label5;
    }

    public function withLabel5(string $value) : Dimension
    {
        $clone = clone $this;
        $clone->label5 = $value;
        return $clone;
    }

    public function getIsLocked() : bool
    {
        return $this->is_locked;
    }

    public function withIsLocked(bool $value) : Dimension
    {
        $clone = clone $this;
        $clone->is_locked = $value;
        return $clone;
    }

    public function getEnableComment() : bool
    {
        return $this->enable_comment;
    }

    public function withEnableComment(bool $value) : Dimension
    {
        $clone = clone $this;
        $clone->enable_comment = $value;
        return $clone;
    }

    public function getOnlyTextualFeedback() : bool
    {
        return $this->only_textual_feedback;
    }

    public function withOnlyTextualFeedback(bool $only_textual_feedback) : Dimension
    {
        $clone = clone $this;
        $clone->only_textual_feedback = $only_textual_feedback;
        return $clone;
    }

    public function getIsUsed() : bool
    {
        return $this->is_used;
    }

    public function withIsUsed(bool $value) : Dimension
    {
        $clone = clone $this;
        $clone->is_used = $value;
        return $clone;
    }

    public function getOrdernumber() : int
    {
        return $this->ordernumber;
    }

    public function withOrdernumber(int $value) : Dimension
    {
        $clone = clone $this;
        $clone->ordernumber = $value;
        return $clone;
    }
}

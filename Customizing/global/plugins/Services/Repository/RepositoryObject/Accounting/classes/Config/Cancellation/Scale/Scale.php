<?php

declare(strict_types=1);

namespace CaT\Plugins\Accounting\Config\Cancellation\Scale;

class Scale
{
    protected $id;

    /**
     * @var int
     */
    protected $span_start;
    /**
     * @var int
     */
    protected $span_end;
    /**
     * @var int
     */
    protected $percent;

    public function __construct(
        int $id,
        int $span_start,
        int $span_end,
        int $percent
    ) {
        $this->id = $id;
        $this->span_start = $span_start;
        $this->span_end = $span_end;
        $this->percent = $percent;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getSpanStart() : int
    {
        return $this->span_start;
    }

    public function getSpanEnd() : int
    {
        return $this->span_end;
    }

    public function getPercent() : int
    {
        return $this->percent;
    }

    public function withSpanStart(int $span_start) : Scale
    {
        $clone = clone $this;
        $clone->span_start = $span_start;
        return $clone;
    }

    public function withSpanEnd(int $span_end) : Scale
    {
        $clone = clone $this;
        $clone->span_end = $span_end;
        return $clone;
    }

    public function withPercent(int $percent) : Scale
    {
        $clone = clone $this;
        $clone->percent = $percent;
        return $clone;
    }
}

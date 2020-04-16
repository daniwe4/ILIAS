<?php

declare(strict_types=1);

namespace CaT\Plugins\CourseMember\LPOptions;

/**
 * Immutable object for a single lp option entry
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class LPOption
{
    /**
     * @var id
     */
    protected $id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var int | null
     */
    protected $ilias_lp;

    /**
     * @var bool
     */
    protected $active;

    /**
     * @var bool
     */
    protected $standard;

    public function __construct(int $id, string $title, int $ilias_lp, bool $active, bool $standard)
    {
        $this->id = $id;
        $this->title = $title;
        $this->ilias_lp = $ilias_lp;
        $this->active = $active;
        $this->standard = $standard;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getILIASLP() : int
    {
        return $this->ilias_lp;
    }

    public function getActive() : bool
    {
        return $this->active;
    }

    public function isStandard() : bool
    {
        return $this->standard;
    }

    public function withTitle(string $title) : LPOption
    {
        $clone = clone $this;
        $clone->title = $title;
        return $clone;
    }

    public function withILIASLP(int $ilias_lp) : LPOption
    {
        $clone = clone $this;
        $clone->ilias_lp = $ilias_lp;
        return $clone;
    }

    public function withActive(bool $active) : LPOption
    {
        $clone = clone $this;
        $clone->active = $active;
        return $clone;
    }

    public function withStandard(bool $standard) : LPOption
    {
        $clone = clone $this;
        $clone->standard = $standard;
        return $clone;
    }
}

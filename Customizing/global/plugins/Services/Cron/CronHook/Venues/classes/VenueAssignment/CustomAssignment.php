<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

declare(strict_types=1);

namespace CaT\Plugins\Venues\VenueAssignment;

/**
 * Relates a text as venue to a course.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class CustomAssignment implements VenueAssignment
{

    /**
     * @var int
     */
    protected $crs_id;

    /**
     * @var string
     */
    protected $text;


    public function __construct(int $crs_id, string $text)
    {
        $this->crs_id = $crs_id;
        $this->text = $text;
    }

    /**
     * @inheritdoc
     */
    public function getCrsId() : int
    {
        return $this->crs_id;
    }

    /**
     * @inheritdoc
     */
    public function isListAssignment() : bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function isCustomAssignment() : bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getVenueId() : int
    {
        throw new \LogicException("This is a CustomAssignment. No venue-id in here.");
    }

    /**
     * @inheritdoc
     */
    public function withVenueId(int $id) : VenueAssignment
    {
        throw new \LogicException("This is a CustomAssignment. No venue-id in here.");
    }

    /**
     * @inheritdoc
     */
    public function getVenueText() : string
    {
        return $this->text;
    }

    /**
     * @inheritdoc
     */
    public function withVenueText(string $text) : VenueAssignment
    {
        $clone = clone $this;
        $clone->text = $text;
        return $clone;
    }
}

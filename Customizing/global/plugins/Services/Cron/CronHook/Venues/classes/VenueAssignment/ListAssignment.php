<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

declare(strict_types=1);

namespace CaT\Plugins\Venues\VenueAssignment;

/**
 * Relates a venue from the list to a course.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ListAssignment implements VenueAssignment
{

    /**
     * @var int
     */
    protected $crs_id;

    /**
     * @var string
     */
    protected $venue_id;

    /**
     * @var string | null
     */
    protected $additional_info;

    public function __construct(
        int $crs_id,
        int $venue_id,
        string $additional_info = null
    ) {
        $this->crs_id = $crs_id;
        $this->venue_id = $venue_id;
        $this->additional_info = $additional_info;
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
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isCustomAssignment() : bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getVenueId() : int
    {
        return $this->venue_id;
    }

    /**
     * @inheritdoc
     */
    public function withVenueId(int $venue_id) : VenueAssignment
    {
        $clone = clone $this;
        $clone->venue_id = $venue_id;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getVenueText() : string
    {
        throw new \LogicException("This is a ListAssignment. No venue-text in here.");
    }

    /**
     * @inheritdoc
     */
    public function withVenueText(string $text) : VenueAssignment
    {
        throw new \LogicException("This is a ListAssignment. No venue-text in here.");
    }

    /**
     * @return string | null
     */
    public function getAdditionalInfo()
    {
        return $this->additional_info;
    }

    public function withAdditionalInfo(string $additional_info) : VenueAssignment
    {
        $clone = clone $this;
        $clone->additional_info = $additional_info;
        return $clone;
    }
}

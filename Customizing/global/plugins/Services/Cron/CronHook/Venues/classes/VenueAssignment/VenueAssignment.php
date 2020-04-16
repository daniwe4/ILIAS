<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

declare(strict_types=1);

namespace CaT\Plugins\Venues\VenueAssignment;

/**
 * Interface for the relation of venue and course.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
interface VenueAssignment
{
    public function getCrsId() : int;
    public function isListAssignment() : bool;
    public function isCustomAssignment() : bool;

    /**
     * Get the venue's id from the assignment.
     * Must raise, if this is not a list assignment.
     *
     * @throws LogicException 	if !isListAssignment
     */
    public function getVenueId() : int;

    /**
     * Get a copy of assingment with new venue id
     * Must raise, if this is not a list assignment.
     *
     * @throws LogicException 	if !isListAssignment
     */
    public function withVenueId(int $id) : VenueAssignment;

    /**
     * Get the venue (the custom text) from the assignment.
     * Must raise, if this is not a custom assignment.
     *
     * @throws LogicException 	if !isCustomAssignment
     */
    public function getVenueText() : string;

    /**
     * Get a copy of assingment with new venue text.
     * Must raise, if this is not a custom assignment.
     *
     * @throws LogicException 	if !isCustomAssignment
     */
    public function withVenueText(string $text) : VenueAssignment;
}

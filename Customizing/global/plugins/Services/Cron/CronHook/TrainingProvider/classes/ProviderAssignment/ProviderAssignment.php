<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

declare(strict_types=1);

namespace CaT\Plugins\TrainingProvider\ProviderAssignment;

/**
 * Interface for the relation of provider and course.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
interface ProviderAssignment
{
    /**
     * Get the course's id of this assignment
     */
    public function getCrsId() : int;

    /**
     * There are two types of assignment.
     * Returns true, if this is a ref to the list of providers.
     */
    public function isListAssignment() : bool;

    /**
     * There are two types of assignment.
     * Returns true, if this is a user induced (text-)provider.
     */
    public function isCustomAssignment() : bool;

    /**
     * Get the provider's id from the assignment.
     * Must raise, if this is not a list assignment.
     *
     * @throws LogicException 	if !isListAssignment
     */
    public function getProviderId() : int;

    /**
     * Get a copy of assingment with new provider id
     * Must raise, if this is not a list assignment.
     *
     * @throws LogicException 	if !isListAssignment
     */
    public function withProviderId(int $id) : ProviderAssignment;

    /**
     * Get the provider (the custom text) from the assignment.
     * Must raise, if this is not a custom assignment.
     *
     * @throws LogicException 	if !isCustomAssignment
     */
    public function getProviderText() : string;

    /**
     * Get a copy of assingment with new provider text.
     * Must raise, if this is not a custom assignment.
     *
     * @throws LogicException 	if !isCustomAssignment
     */
    public function withProviderText(string $text) : ProviderAssignment;
}

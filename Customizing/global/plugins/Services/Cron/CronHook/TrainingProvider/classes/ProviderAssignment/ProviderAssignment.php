<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

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
     *
     * @return int
     */
    public function getCrsId();

    /**
     * There are two types of assignment.
     * Returns true, if this is a ref to the list of providers.
     *
     * @return boolean
     */
    public function isListAssignment();

    /**
     * There are two types of assignment.
     * Returns true, if this is a user induced (text-)provider.
     *
     * @return boolean
     */
    public function isCustomAssignment();

    /**
     * Get the provider's id from the assignment.
     * Must raise, if this is not a list assignment.
     *
     * @throws LogicException 	if !isListAssignment
     *
     * @return int
     */
    public function getProviderId();

    /**
     * Get a copy of assingment with new provider id
     * Must raise, if this is not a list assignment.
     *
     * @param int 	$id
     *
     * @throws LogicException 	if !isListAssignment
     *
     * @return ProviderAssignment
     */
    public function withProviderId($id);

    /**
     * Get the provider (the custom text) from the assignment.
     * Must raise, if this is not a custom assignment.
     *
     * @throws LogicException 	if !isCustomAssignment
     *
     * @return string
     */
    public function getProviderText();

    /**
     * Get a copy of assingment with new provider text.
     * Must raise, if this is not a custom assignment.
     *
     * @param string 	$text
     *
     * @throws LogicException 	if !isCustomAssignment
     *
     * @return ProviderAssignment
     */
    public function withProviderText($text);
}

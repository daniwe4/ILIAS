<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace ILIAS\TMS;

use CaT\Ente\ILIAS\Entity;
use ILIAS\UI;
use ILIAS\UI\Implementation\Component\Modal\Modal;

/**
 * This is an information about a course action, noteworthy for a user in some context.
 */
interface CourseAction
{
    /**
     * Get the owner of this action
     *
     * @return Entity
     */
    public function getEntity();

    /**
     * Get the owner of this action
     *
     * @return \ilObject
     */
    public function getOwner();

    /**
     * Get the priority of the step.
     *
     * Lesser priorities means the action will be displayed in later position
     *
     * @return int
     */
    public function getPriority();

    /**
     * Checks the action is allowed for user
     *
     * @param int 	$usr_id 	Id of user the action is requested for
     *
     * @return bool
     */
    public function isAllowedFor($usr_id);

    /**
     * Get the link for the ui control
     *
     * @param \ilCtrl 	$ctrl
     * @param int 	$usr_id
     *
     * @return string
     */
    public function getLink(\ilCtrl $ctrl, $usr_id);

    /**
     * Get the label for the ui control
     *
     * @return string
     */
    public function getLabel();

    /**
     * Does the action provide a modal
     *
     * @return bool
     */
    public function hasModal();

    /**
     * Return the modal for this action
     * @throws LogicException if no modal is available
     */
    public function getModal(\ilCtrl $ctrl, UI\Factory $factory, int $usr_id);

    /**
     * Decides if link open in new tab
     * @return bool
     */
    public function openInNewTab() : bool;
}

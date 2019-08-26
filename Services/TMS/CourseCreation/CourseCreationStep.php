<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

declare(strict_types=1);

use ILIAS\TMS\CourseCreation\Step;
use ILIAS\TMS\CourseCreation\RequestBuilder;

/**
 * This is one step in the course creation process.
 */
abstract class CourseCreationStep implements Step
{
    /**
     * @inheritDoc
     */
    abstract public function getPriority();

    /**
     * @inheritDoc
     */
    abstract public function isApplicable();

    /**
     * @inheritDoc
     */
    abstract public function setUserId($user_id);

    /**
     * @inheritDoc
     */
    abstract public function setRequestBuilder(RequestBuilder $request_builder);

    /**
     * @inheritDoc
     */
    public function needPreviousStepData() : bool
    {
        return false;
    }
}

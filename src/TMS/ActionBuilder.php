<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace ILIAS\TMS;

use CaT\Ente\Component;
use ILIAS\UI;
use ILIAS\UI\Implementation\Component\Modal\Modal;

/**
 * This is an information about a course action, noteworthy for a user in some context.
 */
interface ActionBuilder extends Component
{
    const CONTEXT_SEARCH = 1;
    const CONTEXT_USER_BOOKING = 2;
    const CONTEXT_EMPLOYEE_BOOKING = 3;
    const CONTEXT_EDU_BIO = 4;
    const CONTEXT_EMPOYEE_EDU_BIO = 5;
    const CONTEXT_MY_TRAININGS = 6;
    const CONTEXT_MY_ADMIN_TRAININGS = 7;
    const CONTEXT_SUPERIOR_SEARCH = 8;
    const CONTEXT_TEP_SESSION_DETAILS = 9;

    /**
     * @return CourseAction[]
     */
    public function getCourseActionsFor(
        int $context,
        int $usr_id,
        bool $with_recommendation_action = true
    ) : array;
}

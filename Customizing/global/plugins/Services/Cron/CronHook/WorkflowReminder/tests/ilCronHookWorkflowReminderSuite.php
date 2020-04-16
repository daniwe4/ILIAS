<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;
use CaT\Plugins\WorkflowReminder;

class ilCronHookWorkflowReminderSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        // add each test class of the component
        $suite->addTestSuite(WorkflowReminder\MinMember\MinMemberTest::class);
        $suite->addTestSuite(WorkflowReminder\NotFinalized\CourseMember\NotFinalizedTest::class);
        $suite->addTestSuite(WorkflowReminder\NotFinalized\Webinar\NotFinalizedTest::class);

        return $suite;
    }
}

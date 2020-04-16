<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;
use CaT\Plugins\UserCourseHistorizing;

class ilCronHookUserCourseHistorizingSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        // add each test class of the component
        $suite->addTestSuite(UserCourseHistorizing\HistCases\HistCourseTest::class);
        $suite->addTestSuite(UserCourseHistorizing\HistCases\HistSessionCourseTest::class);
        $suite->addTestSuite(UserCourseHistorizing\HistCases\HistUserCourseTest::class);
        $suite->addTestSuite(UserCourseHistorizing\Digesters\AccomodationDeletedDigesterTest::class);
        $suite->addTestSuite(UserCourseHistorizing\Digesters\AccomodationDigesterTest::class);
        $suite->addTestSuite(UserCourseHistorizing\Digesters\AccountingDeletedDigesterTest::class);
        $suite->addTestSuite(UserCourseHistorizing\Digesters\AccountingModifiedDigesterTest::class);
        $suite->addTestSuite(UserCourseHistorizing\Digesters\BookingModalitiesDigesterTest::class);
        $suite->addTestSuite(UserCourseHistorizing\Digesters\BookingStatusDigesterTest::class);
        $suite->addTestSuite(UserCourseHistorizing\Digesters\CopySettingsDigesterTest::class);
        $suite->addTestSuite(UserCourseHistorizing\Digesters\CourseDatesDigesterTest::class);
        $suite->addTestSuite(UserCourseHistorizing\Digesters\CourseIdDigesterTest::class);
        $suite->addTestSuite(UserCourseHistorizing\Digesters\CreatedTSDigesterTest::class);
        $suite->addTestSuite(UserCourseHistorizing\Digesters\DeletedDigesterTest::class);
        $suite->addTestSuite(UserCourseHistorizing\Digesters\GTIDigesterTest::class);
        $suite->addTestSuite(UserCourseHistorizing\Digesters\IDDDigesterTest::class);
        $suite->addTestSuite(UserCourseHistorizing\Digesters\InsertSessionDigesterTest::class);
        $suite->addTestSuite(UserCourseHistorizing\Digesters\LocalRoleDigesterTest::class);
        $suite->addTestSuite(UserCourseHistorizing\Digesters\MemberlistFinalizedDigesterTest::class);
        $suite->addTestSuite(UserCourseHistorizing\Digesters\MoveSessionDigesterTest::class);
        $suite->addTestSuite(UserCourseHistorizing\Digesters\OvernightsDigesterTest::class);
        $suite->addTestSuite(UserCourseHistorizing\Digesters\ParticipationStatusDigesterTest::class);
        $suite->addTestSuite(UserCourseHistorizing\Digesters\TitleDigesterTest::class);
        $suite->addTestSuite(UserCourseHistorizing\Digesters\UpdateSessionDigesterTest::class);
        $suite->addTestSuite(UserCourseHistorizing\Digesters\UserCancellationFeeDigesterTest::class);
        $suite->addTestSuite(UserCourseHistorizing\Digesters\UserIdDigesterTest::class);
        $suite->addTestSuite(UserCourseHistorizing\Digesters\WBDDigesterTest::class);

        return $suite;
    }
}

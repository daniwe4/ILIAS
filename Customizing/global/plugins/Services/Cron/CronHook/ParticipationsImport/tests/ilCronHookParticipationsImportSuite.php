<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;

class ilCronHookParticipationsImportSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        // add each test class of the component
        require_once "Customizing/global/plugins/Services/Cron/CronHook/ParticipationsImport/tests/DicTest.php";
        require_once "Customizing/global/plugins/Services/Cron/CronHook/ParticipationsImport/tests/Data/SpoutXLSXExtractorTest.php";
        require_once "Customizing/global/plugins/Services/Cron/CronHook/ParticipationsImport/tests/DataTargets/DataTargetsCourseTest.php";
        require_once "Customizing/global/plugins/Services/Cron/CronHook/ParticipationsImport/tests/DataTargets/DataTargetsParticipationTest.php";
        require_once "Customizing/global/plugins/Services/Cron/CronHook/ParticipationsImport/tests/DataSources/DataSourcesParticipationTest.php";
        require_once "Customizing/global/plugins/Services/Cron/CronHook/ParticipationsImport/tests/Filesystem/FilesystemLocatorTest.php";
        require_once "Customizing/global/plugins/Services/Cron/CronHook/ParticipationsImport/tests/Mappings/BookingStatusRelationMappingTest.php";
        require_once "Customizing/global/plugins/Services/Cron/CronHook/ParticipationsImport/tests/Mappings/CSVCourseMappingTest.php";
        require_once "Customizing/global/plugins/Services/Cron/CronHook/ParticipationsImport/tests/Mappings/MappingsConfigTest.php";
        require_once "Customizing/global/plugins/Services/Cron/CronHook/ParticipationsImport/tests/DataSources/DataSourcesConfigTest.php";
        require_once "Customizing/global/plugins/Services/Cron/CronHook/ParticipationsImport/tests/DataSources/DataSourcesCourseTest.php";
        require_once "Customizing/global/plugins/Services/Cron/CronHook/ParticipationsImport/tests/DataSources/DocumentParticipationsSourceTest.php";
        require_once "Customizing/global/plugins/Services/Cron/CronHook/ParticipationsImport/tests/Mappings/ParticipationStatusRelationMappingTest.php";
        require_once "Customizing/global/plugins/Services/Cron/CronHook/ParticipationsImport/tests/DataSources/DocumentCoursesSourceTest.php";
        $suite->addTestSuite(DicTest::class);
        $suite->addTestSuite(SpoutXLSXExtractorTest::class);
        $suite->addTestSuite(DataTargetsCourseTest::class);
        $suite->addTestSuite(DataTargetsParticipationTest::class);
        $suite->addTestSuite(DataSourcesParticipationTest::class);
        $suite->addTestSuite(FilesystemLocatorTest::class);
        $suite->addTestSuite(BookingStatusRelationMappingTest::class);
        $suite->addTestSuite(CSVCourseMappingTest::class);
        $suite->addTestSuite(MappingsConfigTest::class);
        $suite->addTestSuite(DataSourcesConfigTest::class);
        $suite->addTestSuite(DataSourcesCourseTest::class);
        $suite->addTestSuite(DocumentParticipationsSourceTest::class);
        $suite->addTestSuite(ParticipationStatusRelationMappingTest::class);
        $suite->addTestSuite(DocumentCoursesSourceTest::class);

        return $suite;
    }
}

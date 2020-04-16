<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;
use CaT\Plugins\CourseClassification;

class ilRepositoryObjectCourseClassificationSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        // add each test class of the component
        $suite->addTestSuite(CourseClassification\AdditionalLinks\AdditionalLinksTest::class);
        $suite->addTestSuite(CourseClassification\AdditionalLinks\ilDBTest::class);
        $suite->addTestSuite(CourseClassification\Options\Category\CategoryTest::class);
        $suite->addTestSuite(CourseClassification\Options\Category\ilDBTest::class);
        $suite->addTestSuite(CourseClassification\Options\Eduprogram\ilDBTest::class);
        $suite->addTestSuite(CourseClassification\Options\Topic\ilDBTest::class);
        $suite->addTestSuite(CourseClassification\Options\Topic\TopicBackendTest::class);
        $suite->addTestSuite(CourseClassification\Options\Topic\TopicTest::class);
        $suite->addTestSuite(CourseClassification\Options\Type\ilDBTest::class);
        $suite->addTestSuite(CourseClassification\Options\OptionBackendTest::class);
        $suite->addTestSuite(CourseClassification\Options\OptionsTest::class);
        $suite->addTestSuite(CourseClassification\Settings\CourseClassificationTest::class);
        $suite->addTestSuite(CourseClassification\TableProcessing\TableProcessorTest::class);

        return $suite;
    }
}

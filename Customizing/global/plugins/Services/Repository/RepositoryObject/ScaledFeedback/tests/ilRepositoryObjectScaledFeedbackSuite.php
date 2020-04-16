<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use PHPUnit\Framework\TestSuite;
use CaT\Plugins\ScaledFeedback;

class ilRepositoryObjectScaledFeedbackSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();

        // add each test class of the component
        $suite->addTestSuite(ScaledFeedback\Config\Dimensions\DimensionTest::class);
        $suite->addTestSuite(ScaledFeedback\Config\Sets\SetTest::class);
        $suite->addTestSuite(ScaledFeedback\Config\ilDBTest::class);
        $suite->addTestSuite(ScaledFeedback\Feedback\FeedbackTest::class);
        $suite->addTestSuite(ScaledFeedback\Feedback\ilDBTest::class);

        return $suite;
    }
}

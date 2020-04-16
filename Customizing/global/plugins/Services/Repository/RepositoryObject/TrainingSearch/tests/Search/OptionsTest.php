<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\TrainingSearch\Search;

use PHPUnit\Framework\TestCase;

class OptionsTest extends TestCase
{
    public function testSmoke()
    {
        $options = new Options(1, 1);

        $this->assertTrue(is_object($options));
    }

    public function testProperties()
    {
        $user_id = 42;
        $category_ref_id = 23;
        $sortation = Options::SORTATION_CITY_DESC;
        $options = new Options($user_id, $category_ref_id, $sortation);

        $this->assertEquals($user_id, $options->getUserId());
        $this->assertEquals($category_ref_id, $options->getCategoryRefId());
        $this->assertEquals($sortation, $options->getSortation());
        $this->assertNull($options->getTextFilter());
        $this->assertNull($options->getTypeFilter());
        $this->assertNull($options->getTopicFilter());
        $this->assertNull($options->getCategoryFilter());
        $this->assertNull($options->getDurationStartFilter());
        $this->assertNull($options->getDurationEndFilter());
        $this->assertFalse($options->getOnlyBookableFilter());
        $this->assertFalse($options->getIDDRelevantFilter());
        $this->assertNull($options->getVenueSearchTagFilter());
    }

    public function testFilterProperties()
    {
        $options = (new Options(1, 1))
            ->withTextFilter("TITLE")
            ->withTypeFilter("TYPE")
            ->withTopicFilter("TOPIC")
            ->withCategoryFilter("CATEGORY")
            ->withDurationFilter(new \DateTime("1985-04-05"), new \DateTime("1815-12-10"))
            ->withOnlyBookableFilter(true)
            ->withIDDRelevantFilter(true)
            ->withVenueSearchTagFilter(1);

        $this->assertEquals("TITLE", $options->getTextFilter());
        $this->assertEquals("TYPE", $options->getTypeFilter());
        $this->assertEquals("TOPIC", $options->getTopicFilter());
        $this->assertEquals("CATEGORY", $options->getCategoryFilter());
        $this->assertEquals(new \DateTime("1985-04-05"), $options->getDurationStartFilter());
        $this->assertEquals(new \DateTime("1815-12-10"), $options->getDurationEndFilter());
        $this->assertTrue($options->getOnlyBookableFilter());
        $this->assertTrue($options->getIDDRelevantFilter());
        $this->assertEquals(1, $options->getVenueSearchTagFilter());
    }

    public function testWithSortation()
    {
        $user_id = 42;
        $category_ref_id = 23;
        $sortation = Options::SORTATION_CITY_DESC;
        $options = new Options($user_id, $category_ref_id);
        $new_options = $options->withSortation($sortation);

        $this->assertEquals(Options::SORTATION_PERIOD_ASC, $options->getSortation());
        $this->assertEquals($sortation, $new_options->getSortation());
    }

    public function testHasHashStartingWithUserId()
    {
        $user_id = 42;
        $category_ref_id = 23;
        $sortation = Options::SORTATION_CITY_DESC;
        $options = new Options($user_id, $category_ref_id, $sortation);

        $hash = $options->getHash();

        $this->assertRegexp("/{$user_id}_.*/", $hash);
    }

    public function testHashIdentifiesOptions()
    {
        $user_id = 42;
        $category_ref_id = 23;
        $sortation = Options::SORTATION_CITY_DESC;
        $options = new Options($user_id, $category_ref_id, $sortation);
        $options2 = new Options($user_id, $category_ref_id, $sortation);

        $hash = $options->getHash();
        $hash2 = $options2->getHash();

        $this->assertEquals($hash, $hash2);

        $options = $options
            ->withTextFilter("TITLE")
            ->withTypeFilter("TYPE")
            ->withTopicFilter("TOPIC")
            ->withCategoryFilter("CATEGORY")
            ->withDurationFilter(new \DateTime("1985-04-05"), new \DateTime("1815-12-10"))
            ->withOnlyBookableFilter(true)
            ->withIDDRelevantFilter(true)
            ->withVenueSearchTagFilter(1);

        $options2 = $options2
            ->withTextFilter("TITLE")
            ->withTypeFilter("TYPE")
            ->withTopicFilter("TOPIC")
            ->withCategoryFilter("CATEGORY")
            ->withDurationFilter(new \DateTime("1985-04-05"), new \DateTime("1815-12-10"))
            ->withOnlyBookableFilter(true)
            ->withIDDRelevantFilter(true)
            ->withVenueSearchTagFilter(1);

        $hash = $options->getHash();
        $hash2 = $options2->getHash();

        $this->assertEquals($hash, $hash2);
    }
}

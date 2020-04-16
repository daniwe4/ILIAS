<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\CourseClassification\Options\Category;

use CaT\Plugins\CourseClassification\Options\Option;
use CaT\Plugins\CourseClassification\Options\Topic\Topic;

class Category extends Option
{
    /**
     * @var Option
     */
    protected $topics;

    public function __construct(int $id, string $caption, Topic ...$topics)
    {
        parent::__construct($id, $caption);
        $this->topics = $topics;
    }

    /**
     * @return Option[]
     */
    public function getTopics() : array
    {
        return $this->topics;
    }

    public function getTopicsTitleString() : string
    {
        $titles = array_map(function (Topic $topic) {
            return $topic->getCaption();
        }, $this->topics);

        return implode(", ", $titles);
    }

    public function withTopics(Topic ...$topics) : Category
    {
        $clone = clone $this;
        $clone->topics = $topics;
        return $clone;
    }
}

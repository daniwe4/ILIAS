<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\CourseClassification\Options\Topic;

use CaT\Plugins\CourseClassification\Options\Category\Category;
use CaT\Plugins\CourseClassification\Options\Option;

class Topic extends Option
{
    /**
     * @var Option[]
     */
    protected $category;

    public function __construct(int $id, string $caption, Category $category = null)
    {
        parent::__construct($id, $caption);
        $this->category = $category;
    }

    /**
     * @return Category|null
     */
    public function getCategory()
    {
        return $this->category;
    }

    public function withCategory(Category $category = null) : Topic
    {
        $clone = clone $this;
        $clone->category = $category;
        return $clone;
    }
}

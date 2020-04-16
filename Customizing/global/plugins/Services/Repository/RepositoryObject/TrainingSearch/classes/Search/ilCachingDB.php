<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\TrainingSearch\Search;

class ilCachingDB implements DB
{
    /**
     * @var	DB
     */
    protected $other;

    /**
     * @var	ilObjectFactory
     */
    protected $factory;

    /**
     * @var	Cache
     */
    protected $cache;

    public function __construct(DB $other, ilObjectFactory $factory, Cache $cache)
    {
        $this->other = $other;
        $this->factory = $factory;
        $this->cache = $cache;
    }

    /**
     * @return Course[]
     */
    public function getCoursesFor(Options $options) : array
    {
        $hash = $options->getHash();

        $cached = $this->cache->get($hash);
        if ($cached) {
            return array_map(function ($ids) {
                list($obj_id, $ref_id) = $ids;
                return $this->factory->getCourseFor($ref_id, $obj_id);
            }, $cached);
        }

        $courses = $this->other->getCoursesFor($options);

        $ids = array_map(function (Course $crs) {
            return [$crs->getObjId(), $crs->getRefId()];
        }, $courses);
        $this->cache->set($hash, $ids);

        return $courses;
    }
}

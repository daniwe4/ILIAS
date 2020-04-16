<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\TrainingSearch\Search;

// this prevents loading of the original class, which fails due to
// missing traits
class Course
{
    public function getObjId() : int
    {
    }
    public function getRefId() : int
    {
    }
}

use PHPUnit\Framework\TestCase;

class ilCachingDBTest extends TestCase
{
    public function _setUp()
    {
        $this->other = $this->createMock(DB::class);
        $this->factory = $this->createMock(ilObjectFactory::class);
        $this->cache = $this->createMock(Cache::class);
        $this->db = new ilCachingDB($this->other, $this->factory, $this->cache);
    }

    public function testCachesResult()
    {
        $this->_setUp();
        $options = new Options(1, 1);

        $course = $this->createMock(Course::class);
        $result = [$course];

        $this->other->expects($this->once())
            ->method("getCoursesFor")
            ->with($options)
            ->willReturn($result);

        $this->cache->expects($this->once())
            ->method("get")
            ->with($options->getHash())
            ->willReturn(null);


        $obj_id = 42;
        $ref_id = 23;

        $course->expects($this->once())
            ->method("getObjId")
            ->with()
            ->willReturn($obj_id);

        $course->expects($this->once())
            ->method("getRefId")
            ->with()
            ->willReturn($ref_id);

        $this->cache->expects($this->once())
            ->method("set")
            ->with($options->getHash(), [[$obj_id, $ref_id]]);

        $this->factory->expects($this->never())
            ->method("getCourseFor");

        $this->factory->expects($this->never())
            ->method("getCourseClassificationObjFor");

        $real_result = $this->db->getCoursesFor($options);

        $this->assertEquals($result, $real_result);
    }

    public function testReadsCachedResult()
    {
        $this->_setUp();
        $options = new Options(1, 1);

        $this->other->expects($this->never())
            ->method("getCoursesFor");


        $obj_id = 42;
        $ref_id = 23;

        $this->cache->expects($this->once())
            ->method("get")
            ->with($options->getHash())
            ->willReturn([[$obj_id, $ref_id]]);

        $this->cache->expects($this->never())
            ->method("set");

        $this->factory->expects($this->once())
            ->method("getCourseFor")
            ->with($ref_id, $obj_id)
            ->willReturn("FOO");

        $this->factory->expects($this->never())
            ->method("getCourseClassificationObjFor");

        $real_result = $this->db->getCoursesFor($options);

        $this->assertEquals(["FOO"], $real_result);
    }
}

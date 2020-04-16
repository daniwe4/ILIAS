<?php

declare(strict_types=1);

namespace CaT\Plugins\CourseClassification\Settings;

use PHPUnit\Framework\TestCase;
use CaT\Plugins\CourseClassification\AdditionalLinks;

class CourseClassificationTest extends TestCase
{
    public function test_instanceWithAllProperties()
    {
        $obj_id = 2;
        $topic = array(3);
        $type = 4;
        $edu_program = 5;
        $categories = array(1,2,3);
        $content = "content";
        $goals = "goals";
        $preparation = null;
        $method = array(1,2,3);
        $media = array(1,2,3);
        $target_group = array(1,2,3);
        $target_group_description = "description";

        $course_classification = new CourseClassification(
            $obj_id,
            $type,
            $edu_program,
            $topic,
            $categories,
            $content,
            $goals,
            $preparation,
            $method,
            $media,
            $target_group,
            $target_group_description,
            null
        );

        $this->assertEquals(2, $course_classification->getObjId());
        $this->assertEquals($type, $course_classification->getType());
        $this->assertEquals($edu_program, $course_classification->getEduProgram());
        $this->assertEquals($topic, $course_classification->getTopics());
        $this->assertEquals($categories, $course_classification->getCategories());
        $this->assertEquals($content, $course_classification->getContent());
        $this->assertEquals($preparation, $course_classification->getPreparation());
        $this->assertEquals($goals, $course_classification->getGoals());
        $this->assertEquals($method, $course_classification->getMethod());
        $this->assertEquals($media, $course_classification->getMedia());
        $this->assertEquals($target_group, $course_classification->getTargetGroup());
        $this->assertEquals($target_group_description, $course_classification->getTargetGroupDescription());
    }

    public function test_instanceWithNeeded()
    {
        $obj_id = 2;

        $course_classification = new CourseClassification($obj_id);

        $this->assertEquals(2, $course_classification->getObjId());
        $this->assertNull($course_classification->getType());
        $this->assertNull($course_classification->getEduProgram());
        $this->assertNull($course_classification->getTopics());
        $this->assertNull($course_classification->getCategories());
        $this->assertNull($course_classification->getContent());
        $this->assertNull($course_classification->getGoals());
        $this->assertNull($course_classification->getMethod());
        $this->assertNull($course_classification->getMedia());
        $this->assertNull($course_classification->getTargetGroup());
        $this->assertNull($course_classification->getTargetGroupDescription());
    }

    public function test_withType()
    {
        $obj_id = 2;
        $type = 4;

        $course_classification = new CourseClassification($obj_id);
        $n_course_classification = $course_classification->withType($type);
        $this->assertEquals(2, $course_classification->getObjId());
        $this->assertNull($course_classification->getType());

        $this->assertEquals(2, $n_course_classification->getObjId());
        $this->assertEquals($type, $n_course_classification->getType());

        return $n_course_classification;
    }

    /**
     * @depends test_withType
     */
    public function test_withEduProgram($course_classification)
    {
        $edu_program = 5;

        $n_course_classification = $course_classification->withEduProgram($edu_program);
        $this->assertEquals(2, $course_classification->getObjId());
        $this->assertNull($course_classification->getEduProgram());

        $this->assertEquals(2, $n_course_classification->getObjId());
        $this->assertEquals($edu_program, $n_course_classification->getEduProgram());

        return $n_course_classification;
    }

    /**
     * @depends test_withEduProgram
     */
    public function test_withTopic($course_classification)
    {
        $topic = array(3);

        $n_course_classification = $course_classification->withTopics($topic);
        $this->assertEquals(2, $course_classification->getObjId());
        $this->assertNull($course_classification->getTopics());

        $this->assertEquals(2, $n_course_classification->getObjId());
        $this->assertEquals($topic, $n_course_classification->getTopics());

        return $n_course_classification;
    }

    /**
     * @depends test_withTopic
     */
    public function test_withCategories($course_classification)
    {
        $categories = array(1,2,3);

        $n_course_classification = $course_classification->withCategories($categories);
        $this->assertEquals(2, $course_classification->getObjId());

        $this->assertEquals(2, $n_course_classification->getObjId());
        $this->assertEquals($categories, $n_course_classification->getCategories());

        return $n_course_classification;
    }

    /**
     * @depends test_withCategories
     */
    public function test_withContent($course_classification)
    {
        $content = "content";

        $n_course_classification = $course_classification->withContent($content);
        $this->assertEquals(2, $course_classification->getObjId());

        $this->assertEquals(2, $n_course_classification->getObjId());
        $this->assertEquals($content, $n_course_classification->getContent());

        return $n_course_classification;
    }

    /**
     * @depends test_withContent
     */
    public function test_withGoals($course_classification)
    {
        $goals = "goals";

        $n_course_classification = $course_classification->withGoals($goals);
        $this->assertEquals(2, $course_classification->getObjId());

        $this->assertEquals(2, $n_course_classification->getObjId());
        $this->assertEquals($goals, $n_course_classification->getGoals());

        return $n_course_classification;
    }

    /**
     * @depends test_withGoals
     */
    public function test_withMethod($course_classification)
    {
        $method = array(1,2,3);

        $n_course_classification = $course_classification->withMethod($method);
        $this->assertEquals(2, $course_classification->getObjId());
        $this->assertNull($course_classification->getMethod());

        $this->assertEquals(2, $n_course_classification->getObjId());
        $this->assertEquals($method, $n_course_classification->getMethod());

        return $n_course_classification;
    }

    /**
     * @depends test_withMethod
     */
    public function test_withMedia($course_classification)
    {
        $media = array(1,2,3);

        $n_course_classification = $course_classification->withMedia($media);
        $this->assertEquals(2, $course_classification->getObjId());
        $this->assertNull($course_classification->getMedia());

        $this->assertEquals(2, $n_course_classification->getObjId());
        $this->assertEquals($media, $n_course_classification->getMedia());

        return $n_course_classification;
    }

    /**
     * @depends test_withMedia
     */
    public function test_withTargetGroup($course_classification)
    {
        $target_group = array(1,2,3);

        $n_course_classification = $course_classification->withTargetGroup($target_group);
        $this->assertEquals(2, $course_classification->getObjId());
        $this->assertNull($course_classification->getTargetGroup());

        $this->assertEquals(2, $n_course_classification->getObjId());
        $this->assertEquals($target_group, $n_course_classification->getTargetGroup());

        return $n_course_classification;
    }

    /**
     * @depends test_withTargetGroup
     */
    public function test_withTargetGroupDescription($course_classification)
    {
        $target_group_description = "description";

        $n_course_classification = $course_classification->withTargetGroupDescription($target_group_description);
        $this->assertEquals(2, $course_classification->getObjId());
        $this->assertNull($course_classification->getTargetGroupDescription());

        $this->assertEquals(2, $n_course_classification->getObjId());
        $this->assertEquals($target_group_description, $n_course_classification->getTargetGroupDescription());
    }

    /**
     * @depends test_withType
     */
    public function testWithAdditionalLinks($course_classification)
    {
        $al = new AdditionalLinks\AdditionalLink('label', 'url');
        $cc = $course_classification->withAdditionalLinks([$al, $al]);
        $this->assertEquals(
            [$al, $al],
            $cc->getAdditionalLinks()
        );
    }
    /**
     * @depends test_withType
     */
    public function testWithWrongAdditionalLinks($course_classification)
    {
        $this->expectException(\InvalidArgumentException::class);
        $al = new AdditionalLinks\AdditionalLink('label', 'url');
        $cc = $course_classification->withAdditionalLinks([$al, 'something']);
    }
}

<?php

namespace CaT\Plugins\MaterialList\Lists;

/**
 * Single element of a material list
 */
class ListEntry
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $obj_id;

    /**
     * @var int
     */
    protected $number_per_participant;

    /**
     * @var int
     */
    protected $number_per_course;

    /**
     * @var string
     */
    protected $article_number;

    /**
     * @var string
     */
    protected $title;

    public function __construct($id, $obj_id, $number_per_participant, $number_per_course, $article_number, $title)
    {
        assert('is_int($id)');
        assert('is_int($obj_id)');
        assert('is_int($number_per_participant)');
        assert('is_int($number_per_course)');
        assert('is_string($article_number)');
        assert('is_string($title)');

        $this->id = $id;
        $this->obj_id = $obj_id;
        $this->number_per_participant = $number_per_participant;
        $this->number_per_course = $number_per_course;
        $this->article_number = $article_number;
        $this->title = $title;
    }

    /**
     * Get the id of the list entry
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the id of object where list entry is assigned
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->obj_id;
    }

    /**
     * Get the number per participant
     *
     * @return int
     */
    public function getNumberPerParticipant()
    {
        return $this->number_per_participant;
    }

    /**
     * Get the number per course
     *
     * @return int
     */
    public function getNumberPerCourse()
    {
        return $this->number_per_course;
    }

    /**
     * Get the article number
     *
     * @return string
     */
    public function getArticleNumber()
    {
        return $this->article_number;
    }

    /**
     * Get the title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the number per particiapant
     *
     * @param int 	$number_per_participant
     *
     * @return ListEntry
     */
    public function withNumberPerParticipant($number_per_participant)
    {
        assert('is_int($number_per_participant)');
        $clone = clone $this;
        $clone->number_per_participant = $number_per_participant;
        return $clone;
    }

    /**
     * Set the number per Course
     *
     * @param int 	$number_per_course
     *
     * @return ListEntry
     */
    public function withNumberPerCourse($number_per_course)
    {
        assert('is_int($number_per_course)');
        $clone = clone $this;
        $clone->number_per_course = $number_per_course;
        return $clone;
    }

    /**
     * Set the article number
     *
     * @param string 	$article_number
     *
     * @return ListEntry
     */
    public function withArticleNumber($article_number)
    {
        assert('is_string($article_number)');
        $clone = clone $this;
        $clone->article_number = $article_number;
        return $clone;
    }

    /**
     * Set the title
     *
     * @param string 	$title
     *
     * @return ListEntry
     */
    public function withTitle($title)
    {
        assert('is_string($title)');
        $clone = clone $this;
        $clone->title = $title;
        return $clone;
    }
}

<?php

declare(strict_types=1);

namespace CaT\Plugins\MaterialList\Lists;

/**
 * Single element of a material list
 */
class CheckObject
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
     * @var string
     */
    protected $number_per_participant;

    /**
     * @var string
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

    public function __construct(
        int $id,
        int $obj_id,
        string $number_per_participant,
        string $number_per_course,
        string $article_number,
        string $title
    ) {
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
     * @return string
     */
    public function getNumberPerParticipant()
    {
        return $this->number_per_participant;
    }

    /**
     * Get the number per course
     *
     * @return string
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
}

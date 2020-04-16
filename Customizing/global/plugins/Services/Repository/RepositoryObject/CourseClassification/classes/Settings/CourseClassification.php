<?php

namespace CaT\Plugins\CourseClassification\Settings;

use CaT\Plugins\CourseClassification\Options;
use CaT\Plugins\CourseClassification\Helper;
use CaT\Plugins\CourseClassification\AdditionalLinks\AdditionalLink;

/**
 * This is the object for additional settings.
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class CourseClassification
{
    use Helper;

    /**
     * @var
     */
    protected $obj_id;

    /**
     * @var int | null
     */
    protected $type;

    /**
     * @var int | null
     */
    protected $edu_program;

    /**
     * @var int[] | null
     */
    protected $topics;

    /**
     * @var int[] | null
     */
    protected $categories;

    /**
     * @var string | null
     */
    protected $content;

    /**
     * @var string | null
     */
    protected $goals;

    /**
     * @var string | null
     */
    protected $preparation;

    /**
     * @var int[] | null
     */
    protected $method;

    /**
     * @var int[] | null
     */
    protected $media;

    /**
     * @var int[] | null
     */
    protected $target_group;

    /**
     * @var string | null
     */
    protected $target_group_description;

    /**
     * @var Contact
     */
    protected $contact;

    /**
     * @var AdditionalLink[]
     */
    protected $additional_links;

    /**
     * @param int 	$obj_id
     * @param int | null	$type
     * @param int | null	$edu_program
     * @param int[] | null	$topics
     * @param int[] | null	$categories
     * @param int | null	$content
     * @param string | null	$goals
     * @param string | null $preparation
     * @param int[] | null	$method
     * @param int[] | null	$media
     * @param int[] | null	$target_group
     * @param int | null	$target_group_description
     * @param Contact | null $contact
     * @param AdditionalLink[] $additional_links
     */
    public function __construct(
        $obj_id,
        $type = null,
        $edu_program = null,
        array $topics = null,
        array $categories = null,
        $content = null,
        $goals = null,
        $preparation = null,
        array $method = null,
        array $media = null,
        array $target_group = null,
        $target_group_description = null,
        Contact $contact = null,
        array $additional_links = []
    ) {
        assert('is_int($obj_id)');
        $this->obj_id = $obj_id;

        assert('is_int($type) || is_null($target_group_description)');
        $this->type = $type;

        assert('is_int($edu_program) || is_null($edu_program)');
        $this->edu_program = $edu_program;

        assert('$this->checkIntArray($categories)');
        $this->categories = $categories;

        assert('$this->checkIntArray($topics)');
        $this->topics = $topics;

        assert('is_string($content) || is_null($content)');
        $this->content = $content;

        assert('is_string($goals) || is_null($goals)');
        $this->goals = $goals;

        assert('is_string($preparation) || is_null($preparation)');
        $this->preparation = $preparation;

        assert('$this->checkIntArray($method)');
        $this->method = $method;

        assert('$this->checkIntArray($media)');
        $this->media = $media;

        assert('$this->checkIntArray($target_group)');
        $this->target_group = $target_group;

        assert('is_string($target_group_description) || is_null($target_group_description)');
        $this->target_group_description = $target_group_description;

        if (is_null($contact)) {
            $contact = new Contact();
        }
        $this->contact = $contact;

        foreach ($additional_links as $link) {
            if (!$link instanceof AdditionalLink) {
                throw new \InvalidArgumentException("an entry in $additional_links is not of type 'AdditionalLink'", 1);
            }
        }
        $this->additional_links = $additional_links;
    }

    /**
     * Get the obj id
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->obj_id;
    }

    /**
     * Get the type
     *
     * @return int | null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get the edu program
     *
     * @return int | null
     */
    public function getEduProgram()
    {
        return $this->edu_program;
    }

    /**
     * Get the topics
     *
     * @return int[] | null
     */
    public function getTopics()
    {
        return $this->topics;
    }

    /**
     * Get the categories
     *
     * @return int[] | null
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Get the content
     *
     * @return string | null
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get the goals
     *
     * @return string | null
     */
    public function getGoals()
    {
        return $this->goals;
    }

    /**
     * Get the preparations
     *
     * @return string | null
     */
    public function getPreparation()
    {
        return $this->preparation;
    }

    /**
     * Get the method
     *
     * @return int[] | null
     */
    public function getMethod()
    {
        return $this->method;
    }


    /**
     * Get the media
     *
     * @return int[] | null
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * Get the target group
     *
     * @return int[] | null
     */
    public function getTargetGroup()
    {
        return $this->target_group;
    }

    /**
     * Get the description of target groups
     *
     * @return string | null
     */
    public function getTargetGroupDescription()
    {
        return $this->target_group_description;
    }

    /**
     * Get the contact informations
     *
     * @return Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    public function getAdditionalLinks()
    {
        return $this->additional_links;
    }

    /**
     * Get clone of this with new type
     *
     * @param int | null 	$type
     *
     * @return CourseClassification
     */
    public function withType($type = null)
    {
        assert('is_int($type) || is_null($type)');
        $clone = clone $this;
        $clone->type = $type;
        return $clone;
    }

    /**
     * Get clone of this with new edu program
     *
     * @param int | null 	$edu_program
     *
     * @return CourseClassification
     */
    public function withEduProgram($edu_program = null)
    {
        assert('is_int($edu_program) || is_null($edu_program)');
        $clone = clone $this;
        $clone->edu_program = $edu_program;
        return $clone;
    }

    /**
     * Get clone of this with new topics
     *
     * @param int[] | null 	$topics
     *
     * @return CourseClassification
     */
    public function withTopics(array $topics = null)
    {
        assert('$this->checkIntArray($topics)');
        $clone = clone $this;
        $clone->topics = $topics;
        return $clone;
    }

    /**
     * Get clone of this with new categories
     *
     * @param int | null 	$categories
     *
     * @return CourseClassification
     */
    public function withCategories(array $categories = null)
    {
        assert('$this->checkIntArray($categories)');
        $clone = clone $this;
        $clone->categories = $categories;
        return $clone;
    }

    /**
     * Get clone of this with new content
     *
     * @param string | null 	$content
     *
     * @return CourseClassification
     */
    public function withContent($content = null)
    {
        assert('is_string($content) || is_null($content)');
        $clone = clone $this;
        $clone->content = $content;
        return $clone;
    }

    /**
     * Get clone of this with new goals
     *
     * @param string | null 	$goals
     *
     * @return CourseClassification
     */
    public function withGoals($goals = null)
    {
        assert('is_string($goals) || is_null($goals)');
        $clone = clone $this;
        $clone->goals = $goals;
        return $clone;
    }

    /**
     * Get clone of this with new preparation
     *
     * @param string | null 	$preparation
     *
     * @return CourseClassification
     */
    public function withPreparation($preparation = null)
    {
        assert('is_string($preparation) || is_null($preparation)');
        $clone = clone $this;
        $clone->preparation = $preparation;
        return $clone;
    }

    /**
     * Get clone of this with new method
     *
     * @param int[] | null $method
     *
     * @return CourseClassification
     */
    public function withMethod(array $method = null)
    {
        assert('$this->checkIntArray($method)');
        $clone = clone $this;
        $clone->method = $method;
        return $clone;
    }


    /**
     * Get clone of this with new media
     *
     * @param int[] | null 	$media
     *
     * @return CourseClassification
     */
    public function withMedia(array $media = null)
    {
        assert('$this->checkIntArray($media)');
        $clone = clone $this;
        $clone->media = $media;
        return $clone;
    }

    /**
     * Get clone of this with new target group
     *
     * @param int[] | null 	$target_group
     *
     * @return CourseClassification
     */
    public function withTargetGroup(array $target_group = null)
    {
        assert('$this->checkIntArray($target_group)');
        $clone = clone $this;
        $clone->target_group = $target_group;
        return $clone;
    }

    /**
     * Get clone of this with new target group description
     *
     * @param string | null 	$target_group_description
     *
     * @return CourseClassification
     */
    public function withTargetGroupDescription($target_group_description = null)
    {
        assert('is_string($target_group_description) || is_null($target_group_description)');
        $clone = clone $this;
        $clone->target_group_description = $target_group_description;
        return $clone;
    }

    /**
     * Get a clone of this with contact
     *
     * @param Contact 	$contact
     *
     * @return CourseClassification
     */
    public function withContact(Contact $contact)
    {
        $clone = clone $this;
        $clone->contact = $contact;
        return $clone;
    }

    /**
     * @param AdditionalLink[]
     */
    public function withAdditionalLinks(array $additional_links)
    {
        foreach ($additional_links as $link) {
            if (!$link instanceof AdditionalLink) {
                throw new \InvalidArgumentException("an entry in additional_links is not of type 'AdditionalLink'", 1);
            }
        }
        $clone = clone $this;
        $clone->additional_links = $additional_links;
        return $clone;
    }
}

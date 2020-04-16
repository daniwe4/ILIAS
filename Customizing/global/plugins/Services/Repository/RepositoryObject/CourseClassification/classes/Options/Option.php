<?php

namespace CaT\Plugins\CourseClassification\Options;

/**
 * Base class for options on course classifications
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class Option
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $caption;

    /**
     * Constructor of Option
     *
     * @param int 		$id
     * @param string 	$caption
     */
    public function __construct($id, $caption)
    {
        assert('is_int($id)');
        assert('is_string($caption)');

        $this->id = $id;
        $this->caption = $caption;
    }

    /**
     * Get id of the option
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the caption
     *
     * @return string
     */
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * Get a cloned object with new caption
     *
     * @param string 	$caption
     *
     * @return Option
     */
    public function withCaption($caption)
    {
        assert('is_string($caption)');
        $clone = clone $this;
        $clone->caption = $caption;
        return $clone;
    }
}

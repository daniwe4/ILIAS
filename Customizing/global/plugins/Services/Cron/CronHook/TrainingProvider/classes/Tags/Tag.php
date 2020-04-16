<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace CaT\Plugins\TrainingProvider\Tags;

/**
 * Object class for a single tag
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class Tag
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string 	RGB Color
     */
    protected $color;

    public function __construct($id, $name, $color)
    {
        assert('is_string($name)');
        assert('is_string($color)');

        $this->id = $id;
        $this->name = $name;
        $this->color = $color;
    }

    /**
     *
     * @param string 	$name
     *
     * @return Tag
     */
    public function withName($name)
    {
        assert('is_string($name)');
        $clone = clone $this;
        $clone->name = $name;
        return $clone;
    }

    /**
     *
     * @param string 	$color
     *
     * @return Tag
     */
    public function withColorCode($color)
    {
        assert('is_string($color)');
        $clone = clone $this;
        $clone->color = $color;
        return $clone;
    }

    /**
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * @return string
     */
    public function getColorCode()
    {
        return $this->color;
    }
}

<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

declare(strict_types=1);

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

    public function __construct(int $id, string $name, string $color)
    {
        $this->id = $id;
        $this->name = $name;
        $this->color = $color;
    }

    public function withName(string $name) : Tag
    {
        $clone = clone $this;
        $clone->name = $name;
        return $clone;
    }

    public function withColorCode(string $color) : Tag
    {
        $clone = clone $this;
        $clone->color = $color;
        return $clone;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getColorCode() : string
    {
        return $this->color;
    }
}

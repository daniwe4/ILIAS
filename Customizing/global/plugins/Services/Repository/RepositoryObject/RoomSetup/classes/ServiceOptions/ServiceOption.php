<?php

declare(strict_types=1);

namespace CaT\Plugins\RoomSetup\ServiceOptions;

/**
 * Object for a selectable service option
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ServiceOption
{
    /**
     * @var $id
     */
    protected $id;

    /**
     * @var $name
     */
    protected $name;

    /**
     * @var bool
     */
    protected $active;

    public function __construct(int $id, string $name, bool $active = false)
    {
        $this->id = $id;
        $this->name = $name;
        $this->active = $active;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getActive() : bool
    {
        return $this->active;
    }

    public function withName(string $name) : ServiceOption
    {
        $clone = clone $this;
        $clone->name = $name;
        return $clone;
    }

    public function withActive(bool $active) : ServiceOption
    {
        $clone = clone $this;
        $clone->active = $active;
        return $clone;
    }
}

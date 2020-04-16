<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainingStatisticsByOrgUnits\Settings;

class Settings
{
    const AGGREGATE_ID_NONE = "none";

    /**
    * @var int
    */

    protected $id;

    /**
    * @var bool
    */
    protected $is_online;

    /**
    * @var bool
    */
    protected $is_global;

    public function __construct(int $id, bool $is_online = false, bool $is_global = false)
    {
        $this->id = $id;
        $this->is_online = $is_online;
        $this->is_global = $is_global;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function isOnline() : bool
    {
        return $this->is_online;
    }

    public function isGlobal() : bool
    {
        return $this->is_global;
    }

    public function withIsOnline(bool $is_online) : Settings
    {
        $clone = clone $this;
        $clone->is_online = $is_online;
        return $clone;
    }

    public function withIsGlobal(bool $is_global) : Settings
    {
        $clone = clone $this;
        $clone->is_global = $is_global;
        return $clone;
    }
}

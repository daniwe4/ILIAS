<?php declare(strict_types=1);

namespace CaT\Plugins\TrainingStatistics\Settings;

/**
 * This is the object for additional settings.
 *
 * @author
 * @copyright Extended GPL, see LICENSE
 */
class Settings
{
    protected $obj_id;
    protected $aggregate_id;
    protected $online;
    protected $global;


    const AGGREGATE_ID_NONE = 'none';

    public function __construct(
        int $obj_id,
        string $aggregate_id = self::AGGREGATE_ID_NONE,
        bool $online = false,
        bool $global = false
    ) {
        $this->obj_id = $obj_id;
        $this->aggregate_id = $aggregate_id;
        $this->online = $online;
        $this->global = $global;
    }

    public function objId() : int
    {
        return $this->obj_id;
    }

    public function aggregateId() : string
    {
        return $this->aggregate_id;
    }

    public function online() : bool
    {
        return $this->online;
    }

    public function global() : bool
    {
        return $this->global;
    }

    public function withAggregateId(string $aggregate_id) : Settings
    {
        $other = clone $this;
        $other->aggregate_id = $aggregate_id;
        return $other;
    }

    public function withOnline(bool $online) : Settings
    {
        $other = clone $this;
        $other->online = $online;
        return $other;
    }

    public function withGlobal(bool $global) : Settings
    {
        $other = clone $this;
        $other->global = $global;
        return $other;
    }
}

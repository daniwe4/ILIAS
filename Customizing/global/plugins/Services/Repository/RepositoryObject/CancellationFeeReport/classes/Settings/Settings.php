<?php declare(strict_types = 1);
namespace CaT\Plugins\CancellationFeeReport\Settings;

class Settings
{

    /**
     * The obj-id of the plugin object, to which this settings-instance corresponds.
     * @var	int
     */
    protected $id;
    /**
     * @var	bool
     */
    protected $online;
    /**
     * @var	bool
     */
    protected $global;


    public function __construct(int $id, bool $online = false, bool $global = false)
    {
        $this->id = $id;
        $this->online = $online;
        $this->global = $global;
    }

    public function id() : int
    {
        return $this->id;
    }

    public function online() : bool
    {
        return $this->online;
    }

    public function isGlobal() : bool
    {
        return $this->global;
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

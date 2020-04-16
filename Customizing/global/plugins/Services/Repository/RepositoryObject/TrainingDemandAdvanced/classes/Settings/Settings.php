<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainingDemandAdvanced\Settings;

/**
 * This is the object for additional settings.
 */
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

    /**
     * @var array
     */
    protected $local_roles;


    public function __construct(
        int $id,
        bool $online,
        bool $global,
        array $local_roles
    ) {
        $this->id = $id;
        $this->online = $online;
        $this->global = $global;
        $this->local_roles = $local_roles;
    }

    public function id()
    {
        return $this->id;
    }

    public function online()
    {
        return $this->online;
    }

    public function isGlobal()
    {
        return $this->global;
    }

    public function getLocalRoles() : array
    {
        return $this->local_roles;
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

    public function withLocalRoles(array $local_roles) : Settings
    {
        $other = clone $this;
        $other->local_roles = $local_roles;
        return $other;
    }
}

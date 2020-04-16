<?php

declare(strict_types=1);

namespace CaT\Plugins\WBDCommunicator\Config\WBD;

class System
{
    const WBD_LIVE = "wbd_live";
    const WBD_TEST = "wbd_test";

    /**
     * @var string
     */
    protected $name;

    public function __construct(string $name)
    {
        if (!in_array($name, [self::WBD_TEST, self::WBD_LIVE])) {
            throw new \InvalidArgumentException("Unknown wbd system " . $name);
        }

        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    public function isLive() : bool
    {
        return $this->getName() == self::WBD_LIVE;
    }

    public function isTest() : bool
    {
        return $this->getName() == self::WBD_TEST;
    }
}

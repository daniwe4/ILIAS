<?php

namespace CaT\Plugins\EduTracking\Purposes\GTI\Configuration;

/**
 * Immutable object to get configuration values for IDD purpose
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ConfigGTI
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var bool
     */
    protected $available;

    public function __construct(int $id, bool $available)
    {
        $this->id = $id;
        $this->available = $available;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function getAvailable()
    {
        return $this->available;
    }
}

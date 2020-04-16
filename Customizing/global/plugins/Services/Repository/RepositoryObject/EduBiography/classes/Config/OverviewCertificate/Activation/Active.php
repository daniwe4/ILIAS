<?php

declare(strict_types=1);

namespace CaT\Plugins\EduBiography\Config\OverviewCertificate\Activation;

use PHPUnit\Framework\TestCase;

class Active
{
    /**
     * @var bool
     */
    protected $active;

    /**
     * Configuration constructor.
     * @param bool $active
     */
    public function __construct(bool $active)
    {
        $this->active = $active;
    }

    public function isActive() : bool
    {
        return $this->active;
    }
}

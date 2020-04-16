<?php

declare(strict_types=1);

namespace CaT\Plugins\EduBiography\Config\OverviewCertificate\Activation;

use PHPUnit\Framework\TestCase;

class ActiveTest extends TestCase
{
    public function test_create_instance()
    {
        $config = new Active(false);
        $this->assertInstanceOf(Active::class, $config);
        $this->assertFalse($config->isActive());
    }
}

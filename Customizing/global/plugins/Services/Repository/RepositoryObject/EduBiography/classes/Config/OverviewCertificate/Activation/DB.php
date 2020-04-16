<?php

declare(strict_types=1);

namespace CaT\Plugins\EduBiography\Config\OverviewCertificate\Activation;

use PHPUnit\Framework\TestCase;

interface DB
{
    public function insert(bool $active, int $usr_id, \DateTime $date);
    public function select() : Active;
}

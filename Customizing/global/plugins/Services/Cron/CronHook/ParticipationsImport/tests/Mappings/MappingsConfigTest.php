<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase as TestCase;
use CaT\Plugins\ParticipationsImport\Mappings\Config;

class MappingsConfigTest extends TestCase
{
    public function test_create_and_tread()
    {
        $cfg = new Config(123);
        $this->assertEquals($cfg->externUsrIdField(), 123);
    }
}

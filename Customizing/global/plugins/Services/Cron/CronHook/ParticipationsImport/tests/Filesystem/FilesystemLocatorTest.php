<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use CaT\Plugins\ParticipationsImport\Filesystem\FilesystemLocator;
use CaT\Plugins\ParticipationsImport\Filesystem\Locator;
use CaT\Plugins\ParticipationsImport\Filesystem\ConfigStorage;
use CaT\Plugins\ParticipationsImport\Filesystem\Config;

class FilesystemLocatorTest extends TestCase
{
    public function setUp() : void
    {
        $this->cs = $this->createMock(ConfigStorage::class);
        $this->cs->method('loadCurrentConfig')
            ->willReturn(new Config(__DIR__ . '/Fixtures/', 'Import_[INCREMENT].xlsx'));
    }

    public function test_init()
    {
        $l = new FilesystemLocator($this->cs);
        $this->assertInstanceOf(Locator::class, $l);
        return $l;
    }

    /**
     * @depends test_init
     */
    public function test_locate($l)
    {
        $this->assertEquals($l->getCurrentFilePath(), __DIR__ . '/Fixtures/Import_2019-01-03.xlsx');
    }
}

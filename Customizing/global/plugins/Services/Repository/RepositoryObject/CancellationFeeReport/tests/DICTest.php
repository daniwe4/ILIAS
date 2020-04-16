<?php

use CaT\Plugins\CancellationFeeReport as CFR;
use PHPUnit\Framework\TestCase;
use Pimple\Container as PC;

/**
 * @group needsInstalledILIAS
 */
class DICTest extends TestCase
{
    public function setUp() : void
    {
        global $DIC;
        if (!$DIC) {
            require_once 'Services/PHPUnit/classes/class.ilUnitUtil.php';
            ilUnitUtil::performInitialisation();
        }
    }

    public function test_init()
    {
        require_once __DIR__ . '/../classes/class.ilCancellationFeeReportPlugin.php';
        $this->assertInstanceOf(PC::class, \ilCancellationFeeReportPlugin::dic());
    }

    /**
     * @depends test_init
     */
    public function test_settings_repository()
    {
        $this->assertInstanceOf(
            CFR\Settings\SettingsRepository::class,
            \ilCancellationFeeReportPlugin::dic()['Settings.SettingsRepository']
        );
    }

    /**
     * @depends test_init
     */
    public function test_cancellation_fee_report_gui()
    {
        $this->assertInstanceOf(
            \ilCancellationFeeReportGUI::class,
            \ilCancellationFeeReportPlugin::dic()['ilCancellationFeeReportGUI']
        );
    }

    /**
     * @depends test_init
     */
    public function test_settings_gui()
    {
        $this->assertInstanceOf(
            \ilCancellationFeeReportSettingsGUI::class,
            \ilCancellationFeeReportPlugin::dic()['Settings.ilCancellationFeeReportSettingsGUI']
        );
    }

    /**
     * @depends test_init
     */
    public function test_report()
    {
        $this->assertInstanceOf(
            CFR\Report::class,
            \ilCancellationFeeReportPlugin::dic()['Report']
        );
    }

    /**
     * @depends test_init
     */
    public function test_user_orgu_locator()
    {
        $this->assertInstanceOf(
            CFR\UserOrguLocator::class,
            \ilCancellationFeeReportPlugin::dic()['UserOrguLocator']
        );
    }
}

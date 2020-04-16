<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use CaT\Plugins\ParticipationsImport as PI;

/**
 * @group needsInstalledILIAS
 */
class DicTest extends TestCase
{
    protected $backupGlobals = false;

    public static function setUpBeforeClass() : void
    {
        global $DIC;
        if (!$DIC) {
            require_once 'Services/PHPUnit/classes/class.ilUnitUtil.php';
            ilUnitUtil::performInitialisation();
        }
    }

    public function test_init()
    {
        require_once 'Customizing/global/plugins/Services/Cron/CronHook/ParticipationsImport/classes/class.ilParticipationsImportPlugin.php';
        $dic = ilParticipationsImportPlugin::dic();
        $this->assertInstanceOf(Container::class, $dic);
        return $dic;
    }

    /**
     * @depends test_init
     */
    public function test_env($dic)
    {
        $this->assertInstanceOf(ilPlugin::class, $dic['Plugin']);
        $this->assertInstanceOf(ilSetting::class, $dic['ilSetting']);
        $this->assertInstanceOf(ilDBInterface::class, $dic['ilDBInterface']);
    }

    /**
     * @depends test_init
     */
    public function test_data($dic)
    {
        $this->assertInstanceOf(PI\Data\DataExtractor::class, $dic['Data.DataExtractor']);
    }
    /**
     * @depends test_init
     */
    public function test_data_sources($dic)
    {
        $this->assertInstanceOf(
            PI\DataSources\ConfigStorage::class,
            $dic['DataSources.ConfigStorage']
        );
        $this->assertInstanceOf(
            PI\DataSources\CoursesSource::class,
            $dic['DataSources.CoursesSource']
        );
        $this->assertInstanceOf(
            PI\DataSources\ParticipationsSource::class,
            $dic['DataSources.ParticipationsSource']
        );
    }
    /**
     * @depends test_init
     */
    public function test_data_targets($dic)
    {
        $this->assertInstanceOf(
            PI\DataTargets\CoursesTarget::class,
            $dic['DataTargets.CoursesTarget']
        );
        $this->assertInstanceOf(
            PI\DataTargets\ParticipationsTarget::class,
            $dic['DataTargets.ParticipationsTarget']
        );
    }
    /**
     * @depends test_init
     */
    public function test_filesystem($dic)
    {
        $this->assertInstanceOf(
            PI\Filesystem\Locator::class,
            $dic['Filesystem.Locator']
        );
        $this->assertInstanceOf(
            PI\Filesystem\ConfigStorage::class,
            $dic['Filesystem.ConfigStorage']
        );
    }
    /**
     * @depends test_init
     */
    public function test_ilias_utils($dic)
    {
        $this->assertInstanceOf(
            PI\IliasUtils\CourseUtils::class,
            $dic['IliasUtils.CourseUtils']
        );
        $this->assertInstanceOf(
            PI\IliasUtils\UserUtils::class,
            $dic['IliasUtils.UserUtils']
        );
    }
    /**
     * @depends test_init
     */
    public function test_mappings($dic)
    {
        $this->assertInstanceOf(
            PI\Mappings\ConfigStorage::class,
            $dic['Mappings.ConfigStorage']
        );
        $this->assertInstanceOf(
            PI\Mappings\CourseMapping::class,
            $dic['Mappings.CourseMapping']
        );
        $this->assertInstanceOf(
            PI\Mappings\UserMapping::class,
            $dic['Mappings.UserMapping']
        );
    }

    /**
     * @depends test_init
     */
    public function test_translators($dic)
    {
        $this->assertInstanceOf(
            PI\CourseTranslator::class,
            $dic['CourseTranslator']
        );
        $this->assertInstanceOf(
            PI\ParticipationTranslator::class,
            $dic['ParticipationTranslator']
        );
    }

    /**
     * @depends test_init
     */
    public function test_job($dic)
    {
        $this->assertInstanceOf(
            ilCronJob::class,
            $dic['job']
        );
    }
}

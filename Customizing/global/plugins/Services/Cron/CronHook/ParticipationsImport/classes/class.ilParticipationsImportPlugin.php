<?php declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/Filesystem/class.ilPIFilesystemConfiguratorGUI.php';
require_once __DIR__ . '/DataSources/class.ilPIDataSourcesConfiguratorGUI.php';
require_once __DIR__ . '/Mappings/class.ilPIMappingsConfiguratorGUI.php';
require_once __DIR__ . '/Mappings/class.ilPIStatusMappingConfiguratorGUI.php';
require_once __DIR__ . '/Mappings/class.ilPIBookingMappingsConfiguratorGUI.php';
require_once __DIR__ . '/Mappings/class.ilPIParticipationMappingsConfiguratorGUI.php';
require_once __DIR__ . '/class.ilParticipationsImportJob.php';
use Pimple\Container;
use CaT\Plugins\ParticipationsImport as PI;

/**
 * Plugin base class. Keeps all information the plugin needs
 */
class ilParticipationsImportPlugin extends ilCronHookPlugin
{
    /**
     * Get the name of the Plugin
     *
     * @return string
     */
    public function getPluginName()
    {
        return "ParticipationsImport";
    }

    /**
     * Get an array with 1 to n numbers of cronjob objects
     *
     * @return ilCronJob[]
     */
    public function getCronJobInstances()
    {
        return [self::DIC()['job']];
    }

    /**
     * Get a single cronjob object
     *
     * @return ilCronJob
     */
    public function getCronJobInstance($a_job_id)
    {
        return self::DIC()['job'];
    }

    public static function DIC() : Container
    {
        global $DIC;
        $dic = new Container();

        $dic['Plugin'] = function ($dic) {
            return new self();
        };

        $dic['ilSetting'] = function ($dic) use ($DIC) {
            return $DIC['ilSetting'];
        };
        $dic['ilDBInterface'] = function ($dic) use ($DIC) {
            return $DIC['ilDB'];
        };
        $dic['ilTemplate'] = function ($dic) use ($DIC) {
            return $DIC['tpl'];
        };
        $dic['ilCtrl'] = function ($dic) use ($DIC) {
            return $DIC['ilCtrl'];
        };
        $dic['ilTabs'] = function ($dic) use ($DIC) {
            return $DIC['ilTabs'];
        };
        $dic['log'] = function ($dic) use ($DIC) {
            return $DIC['ilLog'];
        };

        $dic['Data.DataExtractor'] = function ($dic) {
            return new PI\Data\SpoutXLSXExtractor();
        };

        $dic['DataSources.ConfigStorage'] = function ($dic) {
            return new PI\DataSources\ilSettingConfigStorage($dic['ilSetting']);
        };
        $dic['DataSources.CoursesSource'] = function ($dic) {
            return new PI\DataSources\DocumentCoursesSource(
                $dic['DataSources.ConfigStorage'],
                $dic['Data.DataExtractor']->withSheet(1),
                $dic['Filesystem.Locator']
            );
        };
        $dic['DataSources.ParticipationsSource'] = function ($dic) {
            return new PI\DataSources\DocumentParticipationsSource(
                $dic['DataSources.ConfigStorage'],
                $dic['Data.DataExtractor']->withSheet(2),
                $dic['Filesystem.Locator']
            );
        };

        $dic['DataTargets.CoursesTarget'] = function ($dic) {
            return new PI\DataTargets\HistCoursesTarget(
                $dic['ilDBInterface']
            );
        };
        $dic['DataTargets.ParticipationsTarget'] = function ($dic) {
            return new PI\DataTargets\HistParticipationsTarget(
                $dic['ilDBInterface']
            );
        };

        $dic['Filesystem.Locator'] = function ($dic) {
            return new PI\Filesystem\FilesystemLocator($dic['Filesystem.ConfigStorage']);
        };
        $dic['Filesystem.ConfigStorage'] = function ($dic) {
            return new PI\Filesystem\ilSettingConfigStorage($dic['ilSetting']);
        };

        $dic['IliasUtils.CourseUtils'] = function ($dic) {
            return new PI\IliasUtils\ilCourseUtils($dic['ilDBInterface']);
        };
        $dic['IliasUtils.UserUtils'] = function ($dic) {
            return new PI\IliasUtils\ilUserUtils($dic['ilDBInterface']);
        };

        $dic['Mappings.ConfigStorage'] = function ($dic) {
            return new PI\Mappings\ilConfigStorage(
                $dic['ilSetting'],
                $dic['ilDBInterface']
            );
        };
        $dic['Mappings.CourseMapping'] = function ($dic) {
            return new PI\Mappings\CSVCourseMapping(
                $dic['Filesystem.ConfigStorage'],
                $dic['IliasUtils.CourseUtils']
            );
        };
        $dic['Mappings.UserMapping'] = function ($dic) {
            return new PI\Mappings\ilUserMapping(
                $dic['Mappings.ConfigStorage'],
                $dic['IliasUtils.UserUtils']
            );
        };

        $dic['CourseTranslator'] = function ($dic) {
            return new PI\CourseTranslator(
                $dic['Mappings.CourseMapping']
            );
        };
        $dic['ParticipationTranslator'] = function ($dic) {
            return new PI\ParticipationTranslator(
                $dic['Mappings.UserMapping'],
                $dic['Mappings.ConfigStorage'],
                $dic['Mappings.CourseMapping']
            );
        };

        $dic['ilPIFilesystemConfiguratorGUI'] = function ($dic) {
            return new ilPIFilesystemConfiguratorGUI(
                $dic['Plugin'],
                $dic['ilCtrl'],
                $dic['ilTemplate'],
                $dic['ilTabs'],
                $dic['Filesystem.ConfigStorage']
            );
        };
        $dic['ilPIDataSourcesConfiguratorGUI'] = function ($dic) {
            return new ilPIDataSourcesConfiguratorGUI(
                $dic['Plugin'],
                $dic['ilCtrl'],
                $dic['ilTemplate'],
                $dic['ilTabs'],
                $dic['DataSources.ConfigStorage']
            );
        };
        $dic['ilPIMappingsConfiguratorGUI'] = function ($dic) {
            return new ilPIMappingsConfiguratorGUI(
                $dic['Plugin'],
                $dic['ilCtrl'],
                $dic['ilTemplate'],
                $dic['ilTabs'],
                $dic['IliasUtils.UserUtils'],
                $dic['Mappings.ConfigStorage'],
                $dic['ilPIBookingMappingsConfiguratorGUI'],
                $dic['ilPIParticipationMappingsConfiguratorGUI']
            );
        };
        $dic['ilPIBookingMappingsConfiguratorGUI'] = function ($dic) {
            return new ilPIBookingMappingsConfiguratorGUI(
                $dic['Plugin'],
                $dic['ilCtrl'],
                $dic['ilTemplate'],
                $dic['Mappings.ConfigStorage']
            );
        };
        $dic['ilPIParticipationMappingsConfiguratorGUI'] = function ($dic) {
            return new ilPIParticipationMappingsConfiguratorGUI(
                $dic['Plugin'],
                $dic['ilCtrl'],
                $dic['ilTemplate'],
                $dic['Mappings.ConfigStorage']
            );
        };

        $dic['job'] = function ($dic) {
            return new ilParticipationsImportJob(
                $dic['DataSources.CoursesSource'],
                $dic['DataSources.ParticipationsSource'],
                $dic['CourseTranslator'],
                $dic['ParticipationTranslator'],
                $dic['DataTargets.CoursesTarget'],
                $dic['DataTargets.ParticipationsTarget'],
                $dic['log']
            );
        };
        return $dic;
    }
}
